<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem;


/**
 * Temporary directory
 *
 * Construct a temporary directory object when you want the directory to be deleted
 * once the object destructs.
 *
 * @author Ross Perkins <ross@vubeology.com>
 */
class TempDirectory {

	/**
	 * Constructor
	 * @param string $dir A temporary directory
	 * @param bool $recursive [optional] Recursively treat all files/sub-directories as temp entities?
	 */
	public function __construct($dir, $recursive=false)
	{
		$this->directory = $dir;
		$this->recursive = $recursive;
	}

	/**
	 * Destructor
	 *
	 * If this is a symlink, remove the symlink.
	 * Else if it is a directory, remove the directory.
	 */
	public function __destruct()
	{
		$is_dir = is_dir($this->directory);
		$is_link = is_link($this->directory);

		if($is_dir || $is_link)
			$this->rmdir($this->directory, $this->recursive);
	}

	/**
	 * Remove the directory
	 *
	 * @param string $dir
	 * @param bool $recursive
	 * @throws Exception
	 */
	private function rmdir($dir, $recursive)
	{
		$is_link = is_link($dir);
		if($is_link)
		{
			if(! unlink($dir))
				throw new Exception("Error unlinking $dir");
		}
		else if(! $recursive)
		{
			if(! rmdir($dir))
				throw new Exception("Error removing temp dir: $dir");
		}
		else
		{
			$dh = opendir($dir);
			if(! $dh)
				throw new Exception("Cannot read temp dir contents for removal: $dir");

			do
			{
				$file = readdir($dh);
				if($file === false)
					break;

				// Don't delete current dir or parent dir (yet)
				if($file === '.' || $file === '..')
					continue;

				$path = $dir .DIRECTORY_SEPARATOR. $file;

				$is_link = is_link($path);
				$is_dir = is_dir($path);

				if($is_dir && ! $is_link)
				{
					$this->rmdir($path, true);
				}
				else // anything else, should be able to unlink
				{
					if(! unlink($path))
						throw new Exception("Cannot remove nested temp file: $path");
				}
			}
			while($file !== false);

			closedir($dh);

			// Now remove the dir itself (non-recursive, it should now be empty)
			$this->rmdir($dir, false);
		}
	}

	/**
	 * The directory we will delete when this object destructs
	 * @var string
	 */
	private $directory;

	/**
	 * Recursively treat all files/sub-dirs as temp files?
	 * @var bool
	 */
	private $recursive;
}