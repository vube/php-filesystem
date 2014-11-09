<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem;


/**
 * File Name Resolver
 * 
 * @author Ross Perkins <ross@vubeology.com>
 */
class FileNameResolver implements iFileNameResolver {

	/**
	 * Resolve a reference file/directory given a $base
	 *
	 * If $reference is a file, return it.
	 *
	 * If $reference is a directory, return the name of the $base file in the
	 * $reference directory.
	 *
	 * @param string $base Base path to use for resolving a directory $reference
	 * @param string $reference A file or directory.
     * @param string $sep Directory separator, defaults to OS setting
	 * <p>
	 * If a file, this is the value that gets returned.
	 * </p>
	 * <p>
	 * If a directory, the basename($base) will be appended to the $reference
	 * directory and that value will be returned.
	 * </p>
	 * <p>
	 * This value is assumed to be a directory if it ends with a
	 * DIRECTORY_SEPARATOR character, even if such a directory does not exist.
	 * </p>
	 *
	 * @return string Resolved name of the referenced file
	 * @throws Exception If $reference is a directory and $base is an empty string
	 */
	public function resolve($base, $reference, $sep=DIRECTORY_SEPARATOR)
	{
		// Treat empty string the same as current dir

		if($reference === '')
			$reference = '.';

		// If $reference is a directory, then we want to look at the same file in
		// this directory as $base

		$refLen = strlen($reference);
		$bEndsWithSlash = (strrpos($reference, $sep) === $refLen-1);

		if($bEndsWithSlash || is_dir($reference))
		{
			// If $reference doesn't end with a directory separator, append one
			if(! $bEndsWithSlash)
				$reference .= $sep;

			// To resolve a directory reference to a $base file, the $base file
			// must have a non-empty basename.
			$basename = basename($base);
			if($basename === '')
				throw new Exception("Cannot resolve a directory reference to an empty filename: $base");

			// Append the filename of $first to the directory $second
			$reference .= $basename;
		}

		return $reference;
	}
}