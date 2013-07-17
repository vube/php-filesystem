<?php
/**
 * Test Bootloader
 *
 * Create the /tmp/vube-php-filesystem directory and chdir to it so any
 * relative files we test will be encapsulated in there.
 *
 * @author Ross Perkins <ross@vubeology.com>
 */

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'autoload.php';

$dir = sys_get_temp_dir() .DIRECTORY_SEPARATOR. 'vube-php-filesystem';

if(! is_dir($dir))
{
	if(! mkdir($dir, 0775, true))
		throw new \Exception("Cannot create temp dir: $dir");
}

if(! chdir($dir))
	throw new \Exception("Cannot chdir to system temp: $dir");
