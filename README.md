Vube php-filesystem
===================

PHP classes for working with files and the file system


Example Usage
-------------

```php
use \Vube\FileSystem\Installer;

$installer = new Installer();

// Explicitly create a dir.  Any we create will ALL have mode 0775
$installer->installDir('/path/to/some/dir', 0775);

// Install files to a dir.
$installer->installFile('file1', '/path/to/some/dir/file1');

// Install files to a dir that doesn't exist.
// The parent dirs are created (modes set based on your umask)
$installer->installFile('file2', '/path/to/some/dir/with/subdirs/file2');
```


Build Status
---------

- master [![Build Status](https://travis-ci.org/vube/php-filesystem.png?branch=master)](https://travis-ci.org/vube/php-filesystem)
- develop [![Build Status](https://travis-ci.org/vube/php-filesystem.png?branch=develop)](https://travis-ci.org/vube/php-filesystem)


Dependencies
------------

- PHP 5.3.2+
- Composer
