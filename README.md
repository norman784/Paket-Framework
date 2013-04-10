# Paket Framework

PHP Framework

## Description

This is one of my first php mvc frameworks, long time I don't use it and is outdated. But now that I've found again maybe I will update it and use on my own projects.

## How it works

This should look like your app after instalation on a shared host

/PK/
/PK_root
/public_html
	/config
	/controller
	/layouts
	/model
	/plugins
	/public
	/view
	/.htaccess
	/bootstrap.php
	/PK_project

Why folder PK and file PK_root are on the user webroot?
- Because PK its the framework core and the initial idea was to have the core in a place accesible for all the apps so we don't need to duplicate the files over and over again, the file PK_root is a flag file that indicates the app where is locate the PK folder so whe can add it to the include_path.

What user has the PK_project file?
- As the PK_root is the flag to determine where is the project files.

At this point the app must be working (I've not tested yet).

## TODO

- Update the readme to reflect the real state of the framework.
- Planning to update some libs to use [composer](http://getcomposer.org) and make it compatible to install via the package manager (Its just and idea).

## The MIT License (MIT)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.