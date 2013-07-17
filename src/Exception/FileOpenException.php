<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\Exception;

use Vube\FileSystem\Exception;


/**
 * Exception opening a file
 *
 * @author Ross Perkins <ross@vubeology.com>
 */
class FileOpenException extends Exception {

	/**
	 * Construct
	 * @link http://php.net/manual/en/exception.construct.php
	 * @param string $filename
	 * @param int $code [optional] The Exception code.
	 * @param Exception $previous [optional] The previous exception used for the exception chaining
	 */
	public function __construct($filename, $code=0, Exception $previous=null)
	{
		$message = "Error opening file: " . $filename;
		parent::__construct($message, $code, $previous);
	}
}
