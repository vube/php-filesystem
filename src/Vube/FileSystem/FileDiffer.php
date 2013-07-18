<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem;

use Vube\FileSystem\Exception\NoSuchFileException;
use Vube\FileSystem\Exception\FileOpenException;
use Vube\FileSystem\Exception\FileReadException;


/**
 * Diff files
 *
 * Compare two files and determine whether they are the same or different.
 *
 * @author Ross Perkins <ross@vubeology.com>
 */
class FileDiffer implements iFileDiffer {

	/**
	 * Read chunk size
	 *
	 * We'll read this number of bytes from the source files at a time.
	 *
	 * @var int
	 */
	const READ_SIZE = 16384; // 16 KB chunks

	/**
	 * Constructor
	 * @param iFileNameResolver $resolver [optional]
	 * <p>
	 * If present, a custom file name resolver.
	 * </p>
	 */
	public function __construct(iFileNameResolver $resolver=null)
	{
		if($resolver === null)
			$resolver = new FileNameResolver();

		$this->oFileNameResolver = $resolver;
	}

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
	public function isDiff($first, $second)
	{
		if(! file_exists($first))
			throw new NoSuchFileException($first);

		$second = $this->oFileNameResolver->resolve($first, $second);

		// If the second file doesn't exist, they're definitely different
		if(! file_exists($second))
			return true;

		// If the file sizes are different, definitely the file contents are different
		if(filesize($first) !== filesize($second))
			return true;

		// File sizes are the same, open the files and look for differences

		if(! ($fh1 = fopen($first, 'rb')))
			throw new FileOpenException($first);

		if(! ($fh2 = fopen($second, 'rb')))
			throw new FileOpenException($second);

		while(! feof($fh1) && ! feof($fh2))
		{
			$data1 = fread($fh1, static::READ_SIZE);
			if(false === $data1)
				throw new FileReadException($first);

			$data2 = fread($fh2, static::READ_SIZE);
			if(false === $data2)
				throw new FileReadException($second);

			if($data1 !== $data2)
				return true;
		}

		return false;
	}

	private $oFileNameResolver;
}