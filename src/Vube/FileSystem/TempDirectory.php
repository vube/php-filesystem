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
	 */
	public function __construct($dir)
	{
		$this->directory = $dir;
	}

	/**
	 * Destructor
	 *
	 * If this is a symlink, remove the symlink.
	 * Else if it is a directory, remove the directory.
	 */
	public function __destruct()
	{
		if(is_link($this->directory))
			unlink($this->directory);

		else if(is_dir($this->directory))
			rmdir($this->directory);
	}

	/**
	 * The directory we will delete when this object destructs
	 * @var string
	 */
	private $directory;
}