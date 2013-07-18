<?php
/**
 * Test Bootloader
 *
 * Create the /tmp/vube-php-filesystem directory and chdir to it so any
 * relative files we test will be encapsulated in there.
 *
 * @author Ross Perkins <ross@vubeology.com>
 */

$composerAutoloadPhp = implode(DIRECTORY_SEPARATOR, array(__DIR__,'..','vendor','autoload.php'));
$loader = require_once $composerAutoloadPhp;


$dir = sys_get_temp_dir() .DIRECTORY_SEPARATOR. 'vube-php-filesystem';

if(! is_dir($dir))
{
	if(! mkdir($dir, 0775, true))
		throw new \Exception("Cannot create temp dir: $dir");
}

if(! chdir($dir))
	throw new \Exception("Cannot chdir to system temp: $dir");
