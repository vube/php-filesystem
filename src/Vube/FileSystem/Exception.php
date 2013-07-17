<?php
/**
 * @author Ross Perkins <ross@vubeology.com>
 */

namespace Vube\FileSystem;


/**
 * Vube FileSystem Exception
 *
 * All exceptions thrown by php-filesystem will derive from this class.
 * In this way you can differentiate between exceptions we throw or
 * exceptions thrown by other code.
 *
 * @author Ross Perkins <ross@vubeology.com>
 */
class Exception extends \Exception {}
