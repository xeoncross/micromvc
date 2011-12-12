## MicroMVC PHP Framework

Why should I use this?

PHP is an **interpreted scripting language** and should not be expected to compile large collections of classes at runtime like other languages such as C. However, many PHP projects simply ignore this and attempt to build web applications with the same massive application design patterns as regular programs. The result is what we have today - websites that just can't handle any decent load.

On the other hand, MicroMVC is built with performance in mind. Easily one of the fastest frameworks ever made among the slue of small PHP frameworks. While most frameworks take 2-6MB of RAM to make a simple database request - MicroMVC can do it in less than .5MB while still using the full ORM.

MicroMVC is also fully PSR-0 compliant which means you can start using Symfony, Zend, Flurish, and other libraries right away!

All class methods are fully documented. Average class size is only 4kb which makes reading the codebase very easy and quick. IDE's such as eclipse or netbeans can pickup on the phpDoc comments to add instant auto-completion to your projects. In addition, full multi-byte string support is built into the system.

## Submodules

This system is built to be used with *other PSR-0 compliant libraries*. These can be used easily by adding submodules to the `Class` directory and adding the correct "namespace" path to the `Config/Config.php` file. For example, to use [Zend Framework 2](https://github.com/zendframework/zf2) run the following commands.

	$ git submodule add git://github.com/zendframework/zf2.git Class/Zend

Then add the following configuration path to the `Config/Config.php` file so the system knows where to load the classes from.

	$config['namespaces'] = array(
		'Zend' => 'Zend/library/Zend/'
	);


This system relies on the [Xeoncross/Micro](https://github.com/Xeoncross/Micro) submodule. After checking out a copy of the system, please run the following commands to pull the submodules.

	$ git submodule init
	$ git submodule update

You can add additional libraries as shown above.

## Requirements

* PHP 5.3+
* Nginx 0.7.x (legacy support for Apache with mod_rewrite)
* PDO if using the Database
* mb_string, gettext, iconv, & SPL classes


## License (MIT License)

Copyright (c) 2011 [David Pennington](http://xeoncross.com)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the 'Software'), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.


