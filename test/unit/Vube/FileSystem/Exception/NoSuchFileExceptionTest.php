<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test\Exception;

use Vube\FileSystem\Exception\NoSuchFileException;


class NoSuchFileExceptionTest extends \PHPUnit_Framework_TestCase {

	public function testConstruct()
	{
		$this->setExpectedException('\\Vube\\FileSystem\\Exception\\NoSuchFileException');
		throw new NoSuchFileException('/path/to/file');
	}
}
