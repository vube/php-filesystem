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
class PathTranslator implements iPathTranslator
{
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
    function translate($path)
    {
        if(static::isWindowsOS())
        {
            if(preg_match('%^([A-Z]):(.*)%', $path, $matches))
            {
                // $path contains something like 'C:' at the start,
                // so it's an absolute path from the root.

                $drive = $matches[1];
                $file = $matches[2];
                $unixFile = str_replace('\\', '/', $file);

                if(static::isWindowsMsys())
                {
                    $path = '/' . strtolower($drive) . $unixFile;
                }
                else if(static::isWindowsCygwin())
                {
                    $path = '/cygdrive/' . strtolower($drive) . $unixFile;
                }
            }
            else
            {
                // $path does not look like 'C:' so we assume it to be relative
                if(static::isWindowsMsys() || static::isWindowsCygwin())
                    $path = str_replace('\\', '/', $path);;
            }
        }

        return $path;
    }

    private static $bIsWindows = null;

    protected static function isWindowsOS()
    {
        if(self::$bIsWindows === null)
            self::$bIsWindows = (strncasecmp(PHP_OS, 'WIN', 3) == 0);

        return self::$bIsWindows;
    }

    protected static function isWindowsMsys()
    {
        return ! empty($_SERVER['MSYSTEM']) && static::isWindowsOS();
    }

    protected static function isWindowsCygwin()
    {
        // TODO: How do we detect Cygwin?  I don't use it so no idea...
        return false && static::isWindowsOS();
    }

}
