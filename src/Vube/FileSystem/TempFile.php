<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem;


/**
 * Temporary file
 *
 * Construct a temporary file object when you want the file to be deleted
 * once the object destructs.
 *
 * @author Ross Perkins <ross@vubeology.com>
 */
class TempFile {

	/**
	 * Constructor
	 * @param string $filename A temporary file
	 */
	public function __construct($filename)
	{
		$this->filename = $filename;
	}

	/**
	 * Destructor
	 *
	 * If the temp file exists, delete it.
	 */
	public function __destruct()
	{
		if(file_exists($this->filename))
			unlink($this->filename);
	}

	/**
	 * The file we will delete when this object destructs
	 * @var string
	 */
	private $filename;
}