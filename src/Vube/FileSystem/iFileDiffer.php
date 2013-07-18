<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem;


/**
 * Diff files
 *
 * Compare two files and determine whether they are the same or different.
 *
 * @author Ross Perkins <ross@vubeology.com>
 */
interface iFileDiffer {

	/**
	 * Are these two files different?
	 *
	 * @param string $first First file to compare; this file MUST exist.
	 * @param string $second Second file or directory to compare.
	 * <p>
	 * If a directory, the basename($first) filename is compared in the
	 * $second directory, and the directory MUST exist.
	 * </p>
	 * <p>
	 * If the $second file does not exist, it is considered to be different.
	 * </p>
	 * @return bool FALSE if both files exist and are identical in contents, else TRUE.
	 *
	 * @throws NoSuchFileException If $first does not exist
	 * @throws FileOpenException If either file cannot be opened
	 * @throws FileReadException If either file cannot be fully read
	 */
	public function isDiff($first, $second);
}