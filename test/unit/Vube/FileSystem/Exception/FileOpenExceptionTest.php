<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test\Exception;

use Vube\FileSystem\Exception\FileOpenException;


class FileOpenExceptionTest extends \PHPUnit_Framework_TestCase {

	public function testConstruct()
	{
		$this->setExpectedException('\\Vube\\FileSystem\\Exception\\FileOpenException');
		throw new FileOpenException('/path/to/file');
	}
}
