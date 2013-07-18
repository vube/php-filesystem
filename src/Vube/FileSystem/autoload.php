<?php
/**
 * Autoloader for Vube\FileSystem\*
 *
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem;

function autoload($class)
{
	$prefix = __NAMESPACE__ . '\\';

	if(0 !== strpos($class, $prefix))
		return;

	$relClass = substr($class, strlen($prefix));
	$file = str_replace('\\', DIRECTORY_SEPARATOR, $relClass) . '.php';

	$path = __DIR__ . DIRECTORY_SEPARATOR . $file;

	if(file_exists($path))
		require_once $path;
}

spl_autoload_register('\\'.__NAMESPACE__.'\\autoload', true);
