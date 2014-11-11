<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test;

use \Vube\FileSystem\PathTranslator;


class WindowsPathTranslator extends PathTranslator {
    static protected function isWindowsOS() { return true; }
    static protected function isWindowsMsys() { return false; }
    static protected function isWindowsCygwin() { return false; }
}

class WindowsMsysPathTranslator extends PathTranslator {
    static protected function isWindowsOS() { return true; }
    static protected function isWindowsMsys() { return true; }
    static protected function isWindowsCygwin() { return false; }
}

class WindowsCygwinPathTranslator extends PathTranslator {
    static protected function isWindowsOS() { return true; }
    static protected function isWindowsMsys() { return false; }
    static protected function isWindowsCygwin() { return true; }
}

class UnixPathTranslator extends PathTranslator {
    static protected function isWindowsOS() { return false; }
}


class PathTranslatorTest extends \PHPUnit_Framework_TestCase {

    public function testWindowsRelativePath()
    {
        $pathIn = 'foo';
        $expect = 'foo';

        $xlate = new WindowsPathTranslator();
        $actual = $xlate->translate($pathIn);

        $this->assertSame($actual, $expect, "vanilla windows relative path translation failed");
    }

    public function testWindowsRelativeSubdirPath()
    {
        $pathIn = 'foo\\bar';
        $expect = 'foo\\bar';

        $xlate = new WindowsPathTranslator();
        $actual = $xlate->translate($pathIn);

        $this->assertSame($actual, $expect, "vanilla windows relative path translation failed");
    }

    public function testWindowsAbsolutePath()
    {
        $pathIn = 'C:\\Windows\\system';
        $expect = 'C:\\Windows\\system';

        $xlate = new WindowsPathTranslator();
        $actual = $xlate->translate($pathIn);

        $this->assertSame($actual, $expect, "vanilla windows absolute path translation failed");
    }

	public function testWindowsMsysRelativePath()
	{
        $pathIn = 'foo';
        $expect = 'foo';

		$xlate = new WindowsMsysPathTranslator();
        $actual = $xlate->translate($pathIn);

		$this->assertSame($actual, $expect, "msys relative path translation failed");
	}

    public function testWindowsMsysRelativeSubdirPath()
    {
        $pathIn = 'foo\\bar';
        $expect = 'foo/bar';

        $xlate = new WindowsMsysPathTranslator();
        $actual = $xlate->translate($pathIn);

        $this->assertSame($actual, $expect, "msys relative path translation failed");
    }

    public function testWindowsMsysAbsolutePath()
    {
        $pathIn = 'C:\\Windows\\system';
        $expect = '/c/Windows/system';

        $xlate = new WindowsMsysPathTranslator();
        $actual = $xlate->translate($pathIn);

        $this->assertSame($actual, $expect, "msys absolute path translation failed");
    }

    public function testWindowsMsysAbsolutePathWithForwardSlashes()
    {
        $pathIn = 'C:/Windows/system';
        $expect = '/c/Windows/system';

        $xlate = new WindowsMsysPathTranslator();
        $actual = $xlate->translate($pathIn);

        $this->assertSame($actual, $expect, "msys absolute path translation failed");
    }

    public function testWindowsCygwinRelativePath()
    {
        $pathIn = 'foo';
        $expect = 'foo';

        $xlate = new WindowsCygwinPathTranslator();
        $actual = $xlate->translate($pathIn);

        $this->assertSame($actual, $expect, "cygwin relative path translation failed");
    }

    public function testWindowsCygwinRelativeSubdirPath()
    {
        $pathIn = 'foo\\bar';
        $expect = 'foo/bar';

        $xlate = new WindowsCygwinPathTranslator();
        $actual = $xlate->translate($pathIn);

        $this->assertSame($actual, $expect, "cygwin relative path translation failed");
    }

    public function testWindowsCygwinAbsolutePath()
    {
        $pathIn = 'C:\\Windows\\system';
        $expect = '/cygdrive/c/Windows/system';

        $xlate = new WindowsCygwinPathTranslator();
        $actual = $xlate->translate($pathIn);

        $this->assertSame($actual, $expect, "cygwin absolute path translation failed");
    }

    public function testWindowsCygwinAbsolutePathWithForwardSlashes()
    {
        $pathIn = 'C:/Windows/system';
        $expect = '/cygdrive/c/Windows/system';

        $xlate = new WindowsCygwinPathTranslator();
        $actual = $xlate->translate($pathIn);

        $this->assertSame($actual, $expect, "cygwin absolute path translation failed");
    }
}
