<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test\Exception;

use Vube\FileSystem\Exception\CreateDirectoryException;


class CreateDirectoryExceptionTest extends \PHPUnit_Framework_TestCase {

	public function testConstruct()
	{
		$this->setExpectedException('\\Vube\\FileSystem\\Exception\\CreateDirectoryException');
		throw new CreateDirectoryException('/path/to/dir');
	}
}
