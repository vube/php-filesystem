<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem;


/**
 * iZip interface
 * 
 * @author Ross Perkins <ross@vubeology.com>
 */
interface iZipper {

	/**
	 * Zip a file
	 *
	 * @param string $sSourceFile Path to the source file
	 * @param string $sDestinationFile [optional] Path to save the zip contents.
	 * <p>
	 * If NULL, a default extension will be appended to $sSourceFile, similar to "$sSourceFile.gz".
	 * </p>
	 * <p>
	 * The default extension is based on the type of zip.
	 * </p>
	 *
	 * @return string Path to the saved zip file
	 * @throws \Exception if anything goes wrong
	 */
	public function zip($sSourceFile, $sDestinationFile=null);
}
