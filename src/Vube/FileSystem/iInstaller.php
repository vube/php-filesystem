<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem;


/**
 * File/Directory Installer interface
 *
 * @author Ross Perkins <ross@vubeology.com>
 */
interface iInstaller {

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
	public function installDir($dir, $mode=0777);

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
	public function installFile($sSourcePath, $sInstallPath);

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
	public function symlink($sOriginal, $sAlias);
}
