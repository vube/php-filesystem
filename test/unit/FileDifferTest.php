<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test;

use \org\bovigo\vfs\vfsStream;
use \Vube\FileSystem\FileDiffer;


class FileDifferTest extends \PHPUnit_Framework_TestCase {

	public function setUp()
	{
		$this->root = vfsStream::setup('root', null, array(
			'test-1.xml' => "<?xml version=\"1.0\"?>\n<root/>", // same as test-2.xml
			'test-2.xml' => "<?xml version=\"1.0\"?>\n<root/>", // same as test-1.xml
			'test-3.xml' => "<?xml version=\"1.0\"?>\n<root diff=\"true\"/>", // DIFFERENT from other files
			'a' => array(
				'test-1.xml' => "<?xml version=\"1.0\"?>\n<a/>", // same as a/test-2.xml
				'test-2.xml' => "<?xml version=\"1.0\"?>\n<a/>", // same as a/test-1.xml
			),
			'b' => array(),
		));
	}

	protected function vfsFilename($file)
	{
		return vfsStream::url('root/'.$file);
	}

	public function testDiffThrowsIfNoSuchFile1()
	{
		$differ = new FileDiffer();

		$this->setExpectedException('\\Vube\\FileSystem\\Exception');
		$differ->isDiff($this->vfsFilename('test-no-such-file.xml'), $this->vfsFilename('test-2.xml'));
	}

	public function testDiffReturnsTrueIfSecondDoesNotExist()
	{
		$differ = new FileDiffer();

		$result = $differ->isDiff($this->vfsFilename('test-1.xml'), $this->vfsFilename('test-no-such-file.xml'));
		$this->assertTrue($result, "Expect comparing a file to a non-existent file returns true");
	}

	public function testReturnsTrueIfDiff()
	{
		$differ = new FileDiffer();

		$isDiff = $differ->isDiff($this->vfsFilename('test-3.xml'), $this->vfsFilename('test-2.xml'));
		$this->assertTrue($isDiff, "Expect these files are different");
	}

	public function testReturnsFalseIfNotDiff()
	{
		$differ = new FileDiffer();

		$isDiff = $differ->isDiff($this->vfsFilename('test-1.xml'), $this->vfsFilename('test-2.xml'));
		$this->assertFalse($isDiff, "Expect these files are not different");
	}
}
