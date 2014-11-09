<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test;

use org\bovigo\vfs\vfsStream;
use Vube\FileSystem\TempFile;
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

	public function testRecursiveTempDirectory()
	{
		vfsStream::create(array(
			'recursive' => array(
				'a' => array(
					'1' => 'file',
					'A' => array(),
				),
				'b' => array(),
			),
		), $this->root);

		$temp = new TempDirectory(vfsStream::url('root/recursive'), true);
		unset($temp);

		$this->assertFalse($this->root->hasChild('recursive'), "recursive dir and its contents must have been deleted");
	}

	public function testSymlinkAsTempDirectory()
	{
        // Symlinks don't work on Windows, so don't test this.
        if(DIRECTORY_SEPARATOR === '\\')
            return;

		$temp = new TempFile('temp'); // remove file after this test

		file_put_contents('temp', 'foo');
		$this->assertFileExists('temp', "We must have created this file");

		if(is_link('alias')) unlink('alias');
		$this->assertFalse(is_link('alias'), "alias must have been deleted");

		symlink('temp', 'alias');
		$this->assertTrue(is_link('alias'), "alias must now have been created");

		$tempDir = new TempDirectory('alias');
		unset($tempDir);

		$this->assertFalse(is_link('alias'), "alias must now have been deleted");
	}

	/**
	 * @var \org\bovigo\vfs\vfsStreamDirectory
	 */
	private $root;
}
