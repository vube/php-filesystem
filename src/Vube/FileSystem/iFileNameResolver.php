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
interface iFileNameResolver {

	/**
	 * Resolve a $reference file/directory given a $base file/directory
	 *
	 * @param string $base Base file/directory to use for resolving a directory $reference
	 * @param string $reference A file or directory to resolve.
     * @param string $sep Directory separator, defaults to OS setting.
	 *
	 * @return string Resolved name of the referenced file
	 * @throws Exception If anything goes wrong resolving the $reference
	 */
	public function resolve($base, $reference, $sep=DIRECTORY_SEPARATOR);
}