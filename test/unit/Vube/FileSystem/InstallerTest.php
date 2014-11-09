<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test;

use \org\bovigo\vfs\vfsStream;
use \Vube\FileSystem\Installer;
use \Vube\FileSystem\TempDirectory;
use \Vube\FileSystem\TempFile;


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
        $test1File = $this->vfsFilename('tmp/test1.txt');
        $newFile = $this->vfsFilename("$nonExistentDir/$file");
		$installer->installFile($test1File, $newFile);

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

	/**
	 * Test creating a symlink to an existing file, where the symlink does not yet exist.
	 * Expect that the symlink is created.
	 */
	public function testSymlinkExistingTargetNewAlias()
	{
        // Symlinks aren't implemented on Windows MSYS, so don't even test this.
        if(DIRECTORY_SEPARATOR === '\\')
            return;

		$tempO = new TempFile('test-original.txt'); // clean this file up after this test
		$tempA = new TempFile('test-alias.txt'); // clean this file up after this test

		file_put_contents('test-original.txt', 'contents');
		if(file_exists('test-alias.txt') || is_link('test-alias.txt')) unlink('test-alias.txt');

		$this->assertTrue(file_exists('test-original.txt'), "Created test-original.txt");
		$this->assertFalse(file_exists('test-alias.txt'), "test-alias.txt file DOES NOT exist");

		$installer = new Installer();
		$installer->symlink('test-original.txt', 'test-alias.txt');

		$this->assertTrue(file_exists('test-alias.txt'), "test-alias.txt symlink exists");
		$this->assertTrue(is_link('test-alias.txt'), "test-alias.txt is a symlink");
	}

	/**
	 * Test creating a symlink to an existing file, where a symlink by this name already exists.
	 * Expect the symlink is updated to point to the new location.
	 */
	public function testSymlinkExistingTargetExistingAlias()
	{
        // Symlinks aren't implemented on Windows MSYS, so don't even test this.
        if(DIRECTORY_SEPARATOR === '\\')
            return;

        $tempO = new TempFile('test-original.txt'); // clean this file up after this test
		$tempA = new TempFile('test-alias.txt'); // clean this file up after this test

		file_put_contents('test-original.txt', 'contents');
		if(file_exists('test-alias.txt') || is_link('test-alias.txt')) unlink('test-alias.txt');
		symlink('test-nonexist.txt', 'test-alias.txt'); // create test-alias.txt -> test-nonexist.txt

		$this->assertTrue(file_exists('test-original.txt'), "Created test-original.txt");
		$this->assertTrue(is_link('test-alias.txt'), "test-alias.txt IS an existing symlink");

		$installer = new Installer();
		$installer->symlink('test-original.txt', 'test-alias.txt');

		$this->assertTrue(file_exists('test-alias.txt'), "test-alias.txt symlink exists");
		$this->assertTrue(is_link('test-alias.txt'), "test-alias.txt is a symlink");
	}

	/**
	 * Test creating a symlink to an existing file, where another file with the symlink name already exists.
	 * Expect that the symlink is created and overwrites the existing file.
	 */
	public function testSymlinkExistingTargetWithAliasOverwriteExistingFile()
	{
        // Symlinks aren't implemented on Windows MSYS, so don't even test this.
        if(DIRECTORY_SEPARATOR === '\\')
            return;

        $tempO = new TempFile('test-original.txt'); // clean this file up after this test
		$tempA = new TempFile('test-alias.txt'); // clean this file up after this test

		file_put_contents('test-original.txt', 'contents');
		file_put_contents('test-alias.txt', 'actual file in the way');

		$this->assertTrue(file_exists('test-original.txt'), "Created test-original.txt");
		$this->assertTrue(file_exists('test-alias.txt'), "test-alias.txt starts as a regular file");

		$installer = new Installer();
		$installer->symlink('test-original.txt', 'test-alias.txt');

		$this->assertTrue(file_exists('test-alias.txt'), "test-alias.txt symlink exists");
		$this->assertTrue(is_link('test-alias.txt'), "test-alias.txt is a symlink");
	}

	/**
	 * Test creating a symlink to a file that does not exist.
	 * Expect that the symlink is created and points to the file that does not exist.
	 */
	public function testSymlinkMissingTargetNewAlias()
	{
        // Symlinks aren't implemented on Windows MSYS, so don't even test this.
        if(DIRECTORY_SEPARATOR === '\\')
            return;

        $tempO = new TempFile('test-original.txt'); // clean this file up after this test
		$tempA = new TempFile('test-alias.txt'); // clean this file up after this test

		if(file_exists('test-missing.txt')) unlink('test-missing.txt');
		if(file_exists('test-alias.txt') || is_link('test-alias.txt')) unlink('test-alias.txt');

		$this->assertFalse(file_exists('test-missing.txt'), "test-missing.txt file DOES NOT exist");
		$this->assertFalse(file_exists('test-alias.txt'), "test-alias.txt file DOES NOT exist");

		$installer = new Installer();
		$installer->symlink('test-missing.txt', 'test-alias.txt');

		$this->assertFalse(file_exists('test-alias.txt'), "test-alias.txt file DOES NOT exist");
		$this->assertTrue(is_link('test-alias.txt'), "test-alias.txt IS a symlink");
	}

    public function testSymlinkThrowsExceptionsOnWindows()
    {
        // Symlinks aren't implemented on Windows MSYS, so don't even test this.
        if(DIRECTORY_SEPARATOR !== '\\')
            return;

        $tempO = new TempFile('test-original.txt'); // clean this file up after this test
        $tempA = new TempFile('test-alias.txt'); // clean this file up after this test

        if(file_exists('test-missing.txt')) unlink('test-missing.txt');
        if(file_exists('test-alias.txt') || is_link('test-alias.txt')) unlink('test-alias.txt');

        $this->assertFalse(file_exists('test-missing.txt'), "test-missing.txt file DOES NOT exist");
        $this->assertFalse(file_exists('test-alias.txt'), "test-alias.txt file DOES NOT exist");

        $installer = new Installer();

        $this->setExpectedException('\\Vube\\FileSystem\\Exception');
        $installer->symlink('test-missing.txt', 'test-alias.txt');
    }

	private $root;
}
