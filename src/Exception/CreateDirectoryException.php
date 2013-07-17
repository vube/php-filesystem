<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\Exception;

use Vube\FileSystem\Exception;


/**
 * Exception creating a directory
 *
 * @author Ross Perkins <ross@vubeology.com>
 */
class CreateDirectoryException extends Exception {

	/**
	 * Construct
	 * @link http://php.net/manual/en/exception.construct.php
	 * @param string $directory
	 * @param int $code [optional] The Exception code.
	 * @param Exception $previous [optional] The previous exception used for the exception chaining
	 */
	public function __construct($directory, $code=0, Exception $previous=null)
	{
		$message = "Error creating directory: " . $directory;
		parent::__construct($message, $code, $previous);
	}
}
