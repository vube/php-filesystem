<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test;

use \org\bovigo\vfs\vfsStream;
use \Vube\FileSystem\Installer;
use \Vube\FileSystem\TempDirectory;


class InstallerTest extends \PHPUnit_Framework_TestCase {

	public function setUp()
	{
		$this->root = vfsStream::setup('root', null, array(
			'tmp' => array(
				'test1.txt' => 'Sample text file',
			),
			'install' => array(
				'read-only.txt' => 'A read only file in a writeable directory',
				'read-only' => array(
					'read-only.txt' => 'A read only file in a read only directory',
				),
			),
			'in-the-way.txt.tmp' => 'Temp file in the way',
			'in-the-way.txt.tmp.tmp' => 'Temp temp file in the way',
		));

		$this->root->getChild('root/install/read-only.txt')->chown(vfsStream::OWNER_ROOT);
		$this->root->getChild('root/install/read-only.txt')->chmod(0444);

		$this->root->getChild('root/install/read-only')->chown(vfsStream::OWNER_ROOT);
		$this->root->getChild('root/install/read-only')->chmod(0555);

		$this->root->getChild('root/install/read-only/read-only.txt')->chmod(0444);
	}

	protected function vfsFilename($file)
	{
		return vfsStream::url('root/'.$file);
	}

	public function testSetTempFileExtension()
	{
		$expected = 'FOO';

		$installer = new Installer();
		$installer->setTempFileExtension($expected);
		$this->assertSame($expected, $installer->getTempFileExtension(), "Temp file extension is set");
	}

	public function testSetMaxFilenameLength()
	{
		$expected = 100;

		$installer = new Installer();
		$installer->setMaxFilenameLength($expected);
		$this->assertSame($expected, $installer->getMaxFilenameLength(), "Max filename length is set");
	}

	public function testMkdirIfNeededWithSingleNonExistentDir()
	{
		$installer = new Installer();
		$r = $installer->installDir(vfsStream::url('root/no-exist'));

		$this->assertTrue($r, "Should have created that dir");
		$this->assertTrue($this->root->hasChild('no-exist'), "Should have this dir we created");
	}

	public function testMkdirIfNeededWithNestedNonExistentDirs()
	{
		$installer = new Installer();
		$r = $installer->installDir(vfsStream::url('root/no-exist/a/b/c/d/e'));

		$this->assertTrue($r, "Should have created that dir");
		$this->assertTrue($this->root->hasChild('no-exist/a/b/c/d/e'), "Should have this dir we created");
	}

	public function testMkdirIfNeededIgnoresEmptyAndSingleDotPaths()
	{
		$installer = new Installer();
		$r = $installer->installDir(vfsStream::url('root/install/empty/.//'));

		$this->assertTrue($r, "Should have created that dir");
		$this->assertTrue($this->root->hasChild('root/install/empty'), "empty dir must exist");
		$this->assertFalse($this->root->getChild('root/install/empty')->hasChildren(),
			"empty directory must not have any child dirs/files");
	}

	public function testMkdirIfNeededWithExistingDir()
	{
		$installer = new Installer();
		$r = $installer->installDir(vfsStream::url('root'));

		$this->assertFalse($r, "Should not have created an already existing dir");
	}

	public function testMkdirIfNeededOnCurrentDir()
	{
		$installer = new Installer();

		$r = $installer->installDir('.');
		$this->assertFalse($r, "Should not have created '.' dir");

		$r = $installer->installDir('');
		$this->assertFalse($r, "Should not have created '' dir");
	}

	public function testMkdirInReadOnlyDirectoryShouldThrowException()
	{
		$installer = new Installer();
		$this->setExpectedException('\\Vube\\FileSystem\\Exception\\CreateDirectoryException');
		$installer->installDir($this->vfsFilename('install/read-only/some/other/dir'));
	}

	public function testFindTempSafeInstallPath()
	{
		$test1 = vfsStream::url('root/test1.txt');

		$installer = new Installer();
		$installer->setTempFileExtension('.tmp');
		$temp = $installer->findTempSafeInstallPath($test1);

		$tempDirname = dirname($temp);
		$tempBasename = basename($temp);

		$this->assertSame(vfsStream::url('root'), $tempDirname, "Temp file must exist in the same directory as $test1");
		$this->assertFalse($this->root->hasChild($tempBasename), "This file must not exist");
		$this->assertSame("test1.txt.tmp", $tempBasename, "Expected temp name created");
	}

	public function testFindTempSafeInstallPathWithFilesInTheWay()
	{
		$test1 = vfsStream::url('root/in-the-way.txt');

		$installer = new Installer();
		$installer->setTempFileExtension('.tmp');
		$temp = $installer->findTempSafeInstallPath($test1);

		$tempDirname = dirname($temp);
		$tempBasename = basename($temp);

		$this->assertSame(vfsStream::url('root'), $tempDirname, "Temp file must exist in the same directory as $test1");
		$this->assertFalse($this->root->hasChild($tempBasename), "This file must not exist");
		$this->assertSame("in-the-way.txt.tmp.tmp.tmp", $tempBasename, "Expected temp name created");
	}

	public function testFindTempSafeInstallPathThrowsExceptionWhenFilenameTooLong()
	{
		$file = vfsStream::url('root/file');
		$tmp = '.tmp';

		$fileLen = strlen($file);
		$extLen = strlen($tmp);

		// Create "file.tmp.tmp"
		vfsStream::create(array(
			"file$tmp" => "file.tmp in the way",
			"file$tmp$tmp" => "file.tmp.tmp in the way",
		), $this->root);

		$installer = new Installer();
		$installer->setTempFileExtension($tmp);
		$installer->setMaxFilenameLength($fileLen+(2*$extLen));

		$this->setExpectedException('\\Vube\\FileSystem\\Exception');
		$installer->findTempSafeInstallPath($file);
	}

	public function testMkdirOnAbsolutePathname()
	{
		$dir = sys_get_temp_dir() .DIRECTORY_SEPARATOR. "test";
		$installer = new Installer();

		$dir = $installer->findTempSafeInstallPath($dir);
		$tempDir = new TempDirectory($dir); // Unused; just want to rmdir() it when this object destructs

		$r = $installer->installDir($dir);

		$this->assertTrue($r, "Should have created absolute dir");
		$this->assertTrue(file_exists($dir), "Dir exists");
		$this->assertTrue(is_dir($dir), "Dir is a dir");
	}

	public function testMkdirOnRelativePathname()
	{
		if(! chdir(sys_get_temp_dir()))
			throw new \Exception("Cannot chdir to sys temp dir");

		$dir = "test";

		$installer = new Installer();

		$dir = $installer->findTempSafeInstallPath($dir);
		$tempDir = new TempDirectory($dir); // Unused; just want to rmdir() it when this object destructs

		$r = $installer->installDir($dir);

		$this->assertTrue($r, "Should have created relative dir");
		$this->assertTrue(file_exists($dir), "Dir exists");
		$this->assertTrue(is_dir($dir), "Dir is a dir");
	}

	/**
	 * Test that an exception is thrown if you try to install a file that does not exist.
	 */
	public function testSafeInstallNonexistentFileThrowsException()
	{
		$installer = new Installer();
		$this->setExpectedException('\\Vube\\FileSystem\\Exception', '', 1);
		$installer->installFile($this->vfsFilename('tmp/no-such-file.txt'), $this->vfsFilename('install/file.txt'));
	}

	/**
	 * Test that directory is created if needed in order to install a file there
	 */
	public function testSafeInstallFileCreatesDirectoryWhenNeeded()
	{
		$nonExistentDir = 'no-such-dir';
		$file = 'test1.txt';

		$this->assertFalse($this->root->hasChild($nonExistentDir), "Non-existent dir must not exist when test begins");

		$installer = new Installer();
		$installer->installFile($this->vfsFilename('tmp/test1.txt'), $this->vfsFilename("$nonExistentDir/$file"));

		$this->assertTrue($this->root->hasChild($nonExistentDir), "Non-existent dir must exist after file install");
		$this->assertTrue($this->root->hasChild("$nonExistentDir/$file"), "File must have been installed");
	}

	/**
	 * Test that trying to overwrite a read-only directory fails
	 */
	public function testSafeInstallFileDoesNotOverwriteReadOnlyDirectory()
	{
		$installer = new Installer();
		$this->setExpectedException('\\Vube\\FileSystem\\Exception', '', 17);
		$installer->installFile($this->vfsFilename('tmp/test1.txt'), $this->vfsFilename('install/read-only/new-file.txt'));
	}

	/**
	 * Test that trying to create directories in a path to which you don't have access throws an exception
	 */
	public function testSafeInstallFileDoesNotCreateDirsWhenThereAreNoPermissions()
	{
		$installer = new Installer();
		$this->setExpectedException('\\Vube\\FileSystem\\Exception', '', 11);
		$installer->installFile($this->vfsFilename('tmp/test1.txt'), $this->vfsFilename('install/read-only/new-dir1/new-dir2/new-file.txt'));
	}

	/**
	 * Test that installing a valid file into an existing dir with appropriate permissions succeeds.
	 */
	public function testSafeInstallFileSuccess()
	{
		$installer = new Installer();
		$installer->installFile($this->vfsFilename('tmp/test1.txt'), $this->vfsFilename('install/test1.txt'));
		$this->assertTrue($this->root->hasChild('root/install/test1.txt'), "Installed file exists");
	}

	private $root;
}
