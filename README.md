Vube php-filesystem
===================

PHP classes for working with files and the file system


Features
--------

- Safely install files on network mounted drives
- Atomic file installs; 100% uptime on production systems
- Atomic symlink modifications; 100% uptime on production systems


Example Usage
-------------


### Install a directory

```php
// Explicitly create a directory.
// ALL parent dirs we create will share mode 0775 as modified by your umask

$installer = new \Vube\FileSystem\Installer();
$installer->installDir('/path/to/some/dir', 0775);
```


### Install files safely and atomically

```php
// Install files into /existing-dir
//
// When installing into subdirs, we create all dirs needed,
// the mode is set by your umask.
//
// File installs are network-safe, providing 100% uptime
// on production systems.

$installer = new \Vube\FileSystem\Installer();

$installer->installFile('file1', '/existing-dir/file1');
$installer->installFile('file2', '/existing-dir/new-dirs-we-create/with/subdirs/file2');
```


### Create/overwrite symlinks atomically

```php
// Create or overwrite /path/to/symlink
//
// If it already exists, it is atomically updated.

$installer = new \Vube\FileSystem\Installer();
$installer->symlink('/path/to/actual', '/path/to/symlink');
```


### Easily diff files

```php
// Compare file1 and file2; are they different?

$differ = new \Vube\FileSystem\FileDiffer();

if($differ->isDiff('file1', 'file2'))
    echo "These files are different.\n";
```


### Easily zip files

```php
// Gzip source.txt and save the result in destination.gz

$zipper = new \Vube\FileSystem\Gzip();
$zipper->zip('source.txt', 'destination.gz');
```


Build Status
---------

- master [![Build Status](https://travis-ci.org/vube/php-filesystem.png?branch=master)](https://travis-ci.org/vube/php-filesystem)
- develop [![Build Status](https://travis-ci.org/vube/php-filesystem.png?branch=develop)](https://travis-ci.org/vube/php-filesystem)


Dependencies
------------

- PHP 5.3.2+
- Composer
