<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test\Exception;

use Vube\FileSystem\Exception\FileReadException;


class FileReadExceptionTest extends \PHPUnit_Framework_TestCase {

	public function testConstruct()
	{
		$this->setExpectedException('\\Vube\\FileSystem\\Exception\\FileReadException');
		throw new FileReadException('/path/to/file');
	}

}
