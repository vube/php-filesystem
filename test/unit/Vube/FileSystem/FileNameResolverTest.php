<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test;

use \org\bovigo\vfs\vfsStream;
use \Vube\FileSystem\FileNameResolver;


class FileNameResolverTest extends \PHPUnit_Framework_TestCase {

	public function setUp()
	{
		$this->root = vfsStream::setup('root', null, array(
			'file-1' => 'file 1',
			'file-2' => 'file 2',
			'a' => array(
				'file-1' => 'file 1 a',
				'file-2' => 'file 2 a',
			),
			'b' => array(),
		));
	}

	protected function vfsFilename($file)
	{
		return vfsStream::url('root/'.$file);
	}

	/**
	 * Test that resolveFile returns the file itself when $second is an existing file
	 */
	public function testSecondaryFileResolution()
	{
		$file1 = $this->vfsFilename('file-1');
		$file2 = $this->vfsFilename('file-2');

		$resolver = new FileNameResolver();
		$result = $resolver->resolve($file1, $file2);

		$this->assertSame($file2, $result, "Expect resolveFile to return $file2");
	}

	/**
	 * Test that resolveFile uses basename($first) when $second is an existing directory
	 */
	public function testSecondaryDirResolutionWithoutTrailingSlash()
	{
		$file1 = $this->vfsFilename('a/file-1');
		$file2 = $this->vfsFilename('b');
		$fileX = $this->vfsFilename('b/file-1'); // expected result

		$resolver = new FileNameResolver();
		$result = $resolver->resolve($file1, $file2, '/');

		$this->assertSame($fileX, $result, "Expect resolveFile to return '$fileX'");
	}

	public function testSecondaryDirResolutionWithTrailingSlash()
	{
		$file1 = $this->vfsFilename('a/file-1');
		$file2 = $this->vfsFilename('b/');
		$fileX = $this->vfsFilename('b/file-1'); // expected result

		$resolver = new FileNameResolver();
		$result = $resolver->resolve($file1, $file2, '/');

		$this->assertSame($fileX, $result, "Expect resolveFile to return '$fileX'");
	}

	/**
	 * Test that resolving a directory reference to a directory nests the basename($base) into the $reference directory
	 */
	public function testResolveDirectoryToDirectoryNestsDirectories()
	{
		$file1 = $this->vfsFilename('a/');
		$file2 = $this->vfsFilename('b/');
		$fileX = $this->vfsFilename('b/a'); // expected result

		$resolver = new FileNameResolver();
		$result = $resolver->resolve($file1, $file2, '/');

		$this->assertSame($fileX, $result, "Expect resolveFile to return '$fileX'");
	}

	/**
	 * Test that resolving a directory reference to an empty base throws an exception
	 */
	public function testResolveDirectoryReferenceToEmptyBaseThrowsException()
	{
		$file1 = '';
		$file2 = $this->vfsFilename('b/');

		$resolver = new FileNameResolver();
		$this->setExpectedException('\\Vube\\FileSystem\\Exception');
		$resolver->resolve($file1, $file2, '/');
	}

	/**
	 * Test that resolveFile treats $file2 as a dir if it ends with '/' even if it does not exist
	 */
	public function testResolveFileWithNonExistentSecondDir()
	{
		$file1 = $this->vfsFilename('a/file-1');
		$file2 = $this->vfsFilename('does-not-exist/');
		$fileX = $this->vfsFilename('does-not-exist/file-1');

		$resolver = new FileNameResolver();
		$result = $resolver->resolve($file1, $file2, '/');

		$this->assertSame($fileX, $result, "Expect resolveFile to return '$fileX'");
	}

}
