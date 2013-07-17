<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test;


class AutoloadTest extends \PHPUnit_Framework_TestCase {

	public function testAutoloadFileSystem()
	{
		$this->assertTrue(class_exists('\\Vube\\FileSystem\\Exception'),
			"Autoload Exception worked");
	}
}
