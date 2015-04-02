<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem;

use \Vube\FileSystem\Exception\NoSuchFileException;
use \Vube\FileSystem\Exception\CreateDirectoryException;


/**
 * File Installer
 *
 * @author Ross Perkins <ross@vubeology.com>
 */
class Installer implements iInstaller {

	/**
	 * Recursively mkdir with the appropriate $mode
	 *
	 * Note: In PHP 5.4+ mkdir() supposedly sets correct permissions even when recursively
	 * creating directories. However in 5.3 (our minimum target platform) that is not true,
	 * so we do it manually.
	 *
	 * @param string $dir Directory to create. If "." or "" we will not try to create it.
	 * @param int $mode [optional] <p>
	 * The mode is 0777 by default, which means the widest possible
	 * access. For more information on modes, read the details
	 * on the chmod page.
	 * </p>
	 * <p>
	 * mode is ignored on Windows.
	 * </p>
	 * <p>
	 * Note that you probably want to specify the mode as an octal number,
	 * which means it should have a leading zero. The mode is also modified
	 * by the current umask, which you can change using
	 * umask().
	 * </p>
	 *
	 * @return bool TRUE if we created the dir, FALSE if it already existed.
	 *
	 * @see http://php.net/manual/en/function.mkdir.php
	 *
	 * @throws Exception if there is any error while creating directories
	 */
	public function installDir($dir, $mode=0777)
	{
		if('.' === $dir || '' === $dir)
			return false;

		if(is_dir($dir))
			return false;

		$targetDir = $dir;
		$dir = '';

		// If $dir was like "vfs://a/b/c" then we need to
		// set $dir='vfs://' and $parts=array('a','b','c')

		if(substr($targetDir, 0, 6) == 'vfs://')
		{
			$dir = 'vfs://';
            // vfs:// urls always have '/' in them, not '\\' even in Windows
            $parts = explode('/', substr($targetDir, 6));
            $n = count($parts);
		}
        else
        {
            $parts = explode(DIRECTORY_SEPARATOR, $targetDir);
            $n = count($parts);

            // If $dir was like "/absolute/dir" then we need to
            // set $dir='/' and $parts=array('absolute','dir')

            if(strpos($targetDir, DIRECTORY_SEPARATOR) === 0)
            {
                $dir = DIRECTORY_SEPARATOR;
                array_splice($parts, 0, 1);
                $n--;
            }
        }

		$nPartsAdded = 0;

		for($i=0; $i<$n; $i++)
		{
			$next = $parts[$i];

			if($next === '' || $next === '.')
				continue;

			$dir .= ($nPartsAdded ? DIRECTORY_SEPARATOR : '') . $next;
			$nPartsAdded++;

			if(! is_dir($dir))
			{
				if(! mkdir($dir, $mode, false))
					throw new CreateDirectoryException("Failed to create directory $dir (required base of target: $targetDir");
			}
		}

		return true;
	}

	/**
	 * Safely install a file
	 *
	 * This will first make a temporary copy of the file in the same directory as the
	 * new $sInstallPath.  Thus if there are any problems or delay associated with NFS
	 * or other remote-mounted/virtual file systems, the production file will not be
	 * affected during the copy.  Once the file has been totally copied, an atomic
	 * rename will put it into place.
	 *
	 * @param string $sSourcePath
	 * @param string $sInstallPath
	 *
	 * @throws NoSuchFileException If $sSourcePath does not exist
	 * @throws Exception if unable to create parent directories of $sInstallPath
	 * @throws Exception if unable to install temp file and/or unable to rename it to the $sInstallPath
	 */
	public function installFile($sSourcePath, $sInstallPath)
	{
		// If the temp file doesn't exist, we cannot install it
		if(! file_exists($sSourcePath))
			throw new NoSuchFileException($sSourcePath, 1);

		try
		{
			// Create the install directory if needed
			$sInstallDir = dirname($sInstallPath);
			$this->installDir($sInstallDir);
		}
		catch(Exception $e)
		{
			throw new Exception("Cannot install file, no such directory no perms to create it: $sInstallPath", 11, $e);
		}

		// First move temp path up to a temporary filename on the install drive

		$sTempInstallPath = $this->findTempSafeInstallPath($sInstallPath);

		// @silence copy() PHP warnings, we check for failure and throw our own exception
        $success = true;
        $originalUmask = umask(0377); // set secure copy() permissions (read-only by current user, nobody else has ANY perms on it at all)
		if(! @copy($sSourcePath, $sTempInstallPath))
            $success = false;
        umask($originalUmask); // reinstate original umask

        if(! $success)
            throw new Exception("Unable to copy file to temp install path: $sTempInstallPath", 17);

        // Explicitly set the file owner and permissions of the newly copied file to be the same
        // as the source path.  PHP copy() seems to disregard the settings of the source file and
        // instead use the current proc's umask() and user identity.

        clearstatcache(); // We don't care what the cache says, we want to know the current stat info

        $hSrcStat = stat($sSourcePath);
        $sSrcOwner = $hSrcStat['uid'];
        $sSrcGroup = $hSrcStat['gid'];
        $sSrcMode = $hSrcStat['mode'];

        $hDstStat = stat($sTempInstallPath);
        $sDstOwner = $hDstStat['uid'];
        $sDstGroup = $hDstStat['gid'];
        $sDstMode = $hDstStat['mode'];

        if($sSrcOwner !== $sDstOwner)
            @chown($sTempInstallPath, $sSrcOwner);

        if($sSrcGroup !== $sDstGroup)
            @chgrp($sTempInstallPath, $sSrcGroup);

        if($sSrcMode !== $sDstMode)
            @chmod($sTempInstallPath, $sSrcMode);

        // Now rename the temp path to the final location
		// @silence rename() PHP warnings, we check for failure and throw our own exception
		if(! @rename($sTempInstallPath, $sInstallPath))
			throw new Exception("Unable to rename temp file to $sInstallPath", 21);
	}

	/**
	 * Atomic symlink
	 *
	 * This allows us to create symlinks that are moved into place atomically.
	 * If an existing symlink exists, it will be replaced.
	 * If a file exists at the symlink location, it will be overwritten.
	 *
	 * @param string $sOriginal Path to the original file
	 * @param string $sAlias Name of the alias file
	 * @throws Exception if there is any error
	 *
	 * @since 0.1.2
	 */
	public function symlink($sOriginal, $sAlias)
	{
		$sTempAlias = $this->findTempSafeInstallPath($sAlias);

		// @silence symlink warnings, we check for failure and throw our own exception
		if(! @symlink($sOriginal, $sTempAlias))
			throw new Exception("Failed to create symlink to $sOriginal");

		// @silence rename warnings, we check for failure and throw our own exception
		if(! @rename($sTempAlias, $sAlias))
			throw new Exception("Failed to rename temp symlink to $sAlias");
	}

	/**
	 * Find an unused temporary safe install path
	 *
	 * @param string $sInstallPath The path where we really want to install this file
	 * @return string A temp filename in the same directory as $sInstallPath
	 */
	public function findTempSafeInstallPath($sInstallPath)
	{
		try
		{
			$sTemp = $this->appendTempExtension($sInstallPath);

			while(file_exists($sTemp))
			{
				$sTemp = $this->appendTempExtension($sTemp);
			}
		}
		catch(Exception $e)
		{
			throw new Exception("No available temp file found for: $sInstallPath", 0, $e);
		}

		return $sTemp;
	}

	/**
	 * Append the temp filename extension to $sFile
	 *
	 * @param string $sFile Original file name
	 * @return string $sFile with the temp extension appended
	 *
	 * @throws Exception if the resulting filename exceeds the maximum filename length
	 */
	protected function appendTempExtension($sFile)
	{
		if($this->sTempFileExtension === '')
			throw new Exception("Programmer error: Empty temp file extension");

		$sFile .= $this->sTempFileExtension;

		$len = strlen($sFile);
		if($len > $this->nMaxFilenameLength)
			throw new Exception("Max filename length (".$this->nMaxFilenameLength.") exceeded: ".$sFile);

		return $sFile;
	}

	/**
	 * Get the maximum filename length
	 * @return int the maximum filename length
	 */
	public function getMaxFilenameLength()
	{
		return $this->nMaxFilenameLength;
	}

	/**
	 * Set the maximum filename length
	 * @param int $len Maximum filename length
	 * @return void
	 */
	public function setMaxFilenameLength($len)
	{
		$this->nMaxFilenameLength = $len;
	}

	/**
	 * Get the temp filename extension
	 * @return string Temp filename extension
	 */
	public function getTempFileExtension()
	{
		return $this->sTempFileExtension;
	}

	/**
	 * Set the temp filename extension
	 * @param string $ext Temp filename extension (include "." or "-" if you want it).
	 * @return void
	 */
	public function setTempFileExtension($ext)
	{
		$this->sTempFileExtension = $ext;
	}

	/**
	 * Maximum Filename Length
	 * @var int
	 */
	private $nMaxFilenameLength = 255;

	/**
	 * Temp File Extension
	 *
	 * This value must include a "." or "-" character if you want one.
	 * @var string
	 */
	private $sTempFileExtension = '.tmp';
}
