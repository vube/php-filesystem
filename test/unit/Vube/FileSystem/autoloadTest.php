<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem\test;

// Composer's autoload.php will handle \Vube\Sitemap\* classes,
// however we want to unit test our autoload.php in case people aren't using composer.
//
// Thus, explicitly include it here, unregister the autoloader it sets up,
// and re-register it as the FIRST autoloader in effect.

$autoloadPhp = implode(DIRECTORY_SEPARATOR, array(__DIR__,'..','..','..','..','src','Vube','FileSystem','autoload.php'));
require_once $autoloadPhp;

spl_autoload_unregister('\\Vube\\FileSystem\\autoload'); // remove it from the END of the autoloaders
spl_autoload_register('\\Vube\\FileSystem\\autoload', true, true); // insert it as the FIRST autoloader, for unit test purposes


class autoloadTest extends \PHPUnit_Framework_TestCase {

	public function testAutoload()
	{
		$exists = class_exists('\\Vube\\FileSystem\\Exception');
		$this->assertTrue($exists, "Autoload Exception worked");
	}
}
