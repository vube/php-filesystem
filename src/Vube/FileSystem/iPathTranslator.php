<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem;


/**
 * Path Translator
 * 
 * @author Ross Perkins <ross@vubeology.com>
 */
interface iPathTranslator {

    /**
     * Translate a Windows path into a POSIX path, if necessary.
     *
     * On POSIX systems this does nothing.
     *
     * On Windows+MSYS:   "C:\Windows" => "/c/Windows"
     * On Windows+Cygwin: "C:\Windows" => "/cygdrive/c/Windows"
     *
     * @param string $path The input path which may be absolute or relative
     * @return string POSIX path
     */
    public function translate($path);
}