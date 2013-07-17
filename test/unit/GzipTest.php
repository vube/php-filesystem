<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test;

use Vube\FileSystem\TempFile;
use \Vube\FileSystem\Gzip;


/**
 * Zipper test
 *
 * Note: vfsStream doesn't work with this class, since Zipper uses gzopen()
 * which is incompatible with userland streams.
 *
 * Thus this test actually uses the real filesystem.  This is not an oversight,
 * it's a workaround to a vfsStream issue.
 *
 * @see https://github.com/mikey179/vfsStream/issues/3
 *
 * @author Ross Perkins <ross@vubeology.com>
 */
class GzipTest extends \PHPUnit_Framework_TestCase {

	public function setUp()
	{
		// Delete this temp file when this object destructs
		$this->tempFiles = array(
			new TempFile('test-input.xml'),
		);

		file_put_contents('test-input.xml', 'example test intput xml file');
	}

	public function testDefaultZipFileCreated()
	{
		$input = 'test-input.xml';
		$expectedOutputFile = 'test-input.xml.gz';
		$tempFile = new TempFile($expectedOutputFile); // Unused; just want to destroy this temp file

		$gzip = new Gzip();
		$output = $gzip->zip($input);

		$this->assertSame($expectedOutputFile, $output, "Default output file name returned");
		$this->assertTrue(file_exists($expectedOutputFile), "Default output file was created");
	}

	public function testCustomZipFileCreated()
	{
		$input = 'test-input.xml';
		$customOutputFile = 'test-custom.xml.gz';
		$tempFile = new TempFile($customOutputFile); // Unused; just want to destroy this temp file

		$gzip = new Gzip();
		$output = $gzip->zip($input, $customOutputFile);

		$this->assertSame($customOutputFile, $output, "Custom output file name returned");
		$this->assertTrue(file_exists($customOutputFile), "Custom output file was created");
	}

	private $tempFiles;
}
