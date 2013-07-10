## MicroMVC PHP Framework

Why should I use this?

PHP is an **interpreted scripting language** and should not be expected to compile large collections of classes at runtime like other languages such as C. However, many PHP projects simply ignore this and attempt to build web applications with the same massive application design patterns as regular programs. The result is what we have today - websites that just can't handle any decent load.

On the other hand, MicroMVC is built with performance in mind. Easily one of the [fastest frameworks](http://www.techempower.com/benchmarks) ever made among the slue of small PHP frameworks. While most frameworks take 2-6MB of RAM to make a simple database request - MicroMVC can do it in less than .5MB while still using the full ORM.

MicroMVC is also fully PSR-0 compliant which means you can start using Symfony, Zend, Flurish, and other libraries right away!

All class methods are fully documented. Average class size is only 4kb which makes reading the codebase very easy and quick. IDE's such as eclipse or netbeans can pickup on the phpDoc comments to add instant auto-completion to your projects. In addition, full multi-byte string support is built into the system.

## Composer

> Warning: require(../vendor/autoload.php): failed to open stream: No such file or directory

MicroMVC requires [Composer](http://getcomposer.org/) to work. Simply install composer and run composer inside the base MicroMVC directory. Composer will fetch the required PHP classes and install them inside the `vendor` directory (including the autoload.php).

    $ composer install

Installation guide for [Linux](http://getcomposer.org/doc/00-intro.md#installation-nix) and [Windows](http://getcomposer.org/doc/00-intro.md#installation-windows).

## Requirements</h3>

* Composer
* PHP 5.3+
* Nginx 0.7.x (legacy support for Apache with mod_rewrite)
* PDO if using the Database
* mb_string, [gettext](http://php.net/gettext), [iconv](http://www.php.net/manual/en/book.iconv.php), [ICU INTL](http://php.net/manual/en/book.intl.php) & SPL classes

## How do I install the [PHP intl](http://php.net/manual/en/book.intl.php) extension?

If you have errors about missing classes (like `Locale`) make sure you have the required PHP extensions installed.

* Ubuntu/Debian: `$ sudo apt-get install php5-intl php5-mcrypt php-gettext`
* Windows: uncomment `extension=php_intl.dll`, `extension=php_mycrypt.dll`, and `extension=php_gettext.dll` in your php.ini

## License

[MicroMVC](http://micromvc.com) is licensed under the Open Source MIT license, so you can use it for any personal or corporate projects totally free!</p>

Built by [David Pennington](http://davidpennington.me)
