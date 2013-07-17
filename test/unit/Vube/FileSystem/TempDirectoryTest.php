<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test;

use org\bovigo\vfs\vfsStream;
use Vube\FileSystem\TempDirectory;


class TempDirectoryTest extends \PHPUnit_Framework_TestCase {

	public function setUp()
	{
		$this->root = vfsStream::setup('root', null, array(
			'temp' => array(),
		));
	}

	public function testTempDirectoryDeletedOnUnset()
	{
		$dir = 'temp';
		$url = vfsStream::url('root/'.$dir);
		$this->assertTrue($this->root->hasChild($dir), "Directory exists at start of test");

		$tempDir = new TempDirectory($url);
		unset($tempDir);

		$this->assertFalse($this->root->hasChild($dir), "Directory does NOT exist after unset");
	}

	private function createTempDirectory($dir)
	{
		$url = vfsStream::url('root/'.$dir);

		vfsStream::create(array($dir => array()), $this->root);
		$tempDir = new TempDirectory($url); // unused, we just want it to destruct after function returns

		$this->assertTrue($this->root->hasChild($dir), "Expect we did create this directory: $dir");
	}

	public function testImplicitDestructDeletesTempDirectory()
	{
		$dir = 'temp'.rand().'.txt';
		$this->createTempDirectory($dir);
		$this->assertFalse($this->root->hasChild($dir), "Expect this directory was removed by implicit destruct");
	}

	/**
	 * @var \org\bovigo\vfs\vfsStreamDirectory
	 */
	private $root;
}
