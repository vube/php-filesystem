<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test;

use org\bovigo\vfs\vfsStream;
use Vube\FileSystem\TempFile;


class TempFileTest extends \PHPUnit_Framework_TestCase {

	public function setUp()
	{
		$this->root = vfsStream::setup('root', null, array(
			'temp.txt' => 'a temporary file',
		));
	}

	public function testTempFileDeletedOnUnset()
	{
		$file = 'temp.txt';
		$url = vfsStream::url('root/'.$file);
		$this->assertTrue($this->root->hasChild($file), "File exists at start of test");

		$tempFile = new TempFile($url);
		unset($tempFile);

		$this->assertFalse($this->root->hasChild($file), "File does NOT exist after unset");
	}

	private function createTempFile($file)
	{
		$url = vfsStream::url('root/'.$file);

		vfsStream::create(array($file => 'a temp file'), $this->root);
		$tempFile = new TempFile($url); // unused, we just want it to destruct after function returns

		$this->assertTrue($this->root->hasChild($file), "Expect we did create this file: $file");
	}

	public function testImplicitDestructDeletesTempFile()
	{
		$file = 'temp'.rand().'.txt';
		$this->createTempFile($file);
		$this->assertFalse($this->root->hasChild($file), "Expect this file was removed by implicit destruct");
	}

	/**
	 * @var \org\bovigo\vfs\vfsStreamDirectory
	 */
	private $root;
}
