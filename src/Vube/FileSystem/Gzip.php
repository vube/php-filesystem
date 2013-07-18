<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem;


/**
 * GZip a file
 *
 * @author Ross Perkins <ross@vubeology.com>
 */
class Gzip implements iZipper {

	/**
	 * Read chunk size
	 *
	 * We'll read this number of bytes from the source file at a time,
	 * and write that to the gzip file.
	 *
	 * @var int
	 */
	const READ_SIZE = 16384; // 16 KB chunks

	/**
	 * Gzip a file
	 *
	 * @param string $sSourceFile Path to the source file
	 * @param string $sDestinationFile [optional] Path to save the zip contents.
	 * <p>
	 * If NULL, the default value is $this->getDefaultDestinationFilename($sSourceFile)
	 * </p>
	 *
	 * @return string Path to the saved zip file
	 *
	 * @throws FileOpenException if there is any error opening the source or output files
	 * @throws FileReadException if there is an error reading the source file
	 * @throws FileWriteException if there is an error writing the output file
	 */
	public function zip($sSourceFile, $sDestinationFile=null)
	{
		if($sDestinationFile === null)
			$sDestinationFile = $this->getDefaultDestinationFilename($sSourceFile);

		if(! ($fh = fopen($sSourceFile, 'rb')))
			throw new FileOpenException($sSourceFile);

		if(! ($zp = gzopen($sDestinationFile, 'wb9')))
			throw new FileOpenException($sDestinationFile);

		while(! feof($fh))
		{
			$data = fread($fh, static::READ_SIZE);
			if(false === $data)
				throw new FileReadException($sSourceFile);

			$sz = strlen($data);
			if($sz !== gzwrite($zp, $data, $sz))
				throw new FileWriteException($sDestinationFile);
		}

		gzclose($zp);
		fclose($fh);

		return $sDestinationFile;
	}

	/**
	 * Get the default destination file for this source file
	 * @param string $sSourceFile Path to the source file
	 * @return string Path to the default output file for this source file
	 */
	public function getDefaultDestinationFilename($sSourceFile)
	{
		$sDestinationFile = $sSourceFile . '.gz';
		return $sDestinationFile;
	}
}