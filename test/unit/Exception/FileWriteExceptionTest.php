<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test\Exception;

use Vube\FileSystem\Exception\FileWriteException;


class FileWriteExceptionTest extends \PHPUnit_Framework_TestCase {

	public function testConstruct()
	{
		$this->setExpectedException('\\Vube\\FileSystem\\Exception\\FileWriteException');
		throw new FileWriteException('/path/to/file');
	}
}
