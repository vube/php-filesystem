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
	 * If the temp directory exists, remove it.
	 */
	public function __destruct()
	{
		if(is_dir($this->directory))
			rmdir($this->directory);
	}

	/**
	 * The directory we will delete when this object destructs
	 * @var string
	 */
	private $directory;
}