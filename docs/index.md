Vatsimphp Documentation
=======================

## Requirements

Vatsimphp requires PHP 7.2 or above. If you are running an older PHP version you can use Vatsimphp `^1.0` instead.
However we highly recommend you upgrade your PHP environment to a [support PHP version](https://www.php.net/supported-versions.php).
Vatsimphp will note make any bug fixes related to the usage of an unsupported PHP version.

## Installation

Assuming you have already installed composer, run:

``` bash
$ composer require skymeyer/vatsimphp ^2.0
```

Or add vatsimphp manually to your composer.json of your current project:

``` json
{
    "require": {
        "skymeyer/vatsimphp": "^2.0"
    }
}
```

## Tutorial

We have compiled a full tutorial for newbie users covering the installation process and some real life examples on how to make use of vatsimphp. This tutorial is mostly targetted for novice users, however it contains valuable information about the vatsimphp design for everyone.

[Vatsimphp tutorial](https://github.com/skymeyer/Vatsimphp/blob/master/docs/tutorial.md)


## Easy API usage

The *easy API* is mostly covered in the above tutorial. For a reference checkout our
[examples](https://github.com/skymeyer/Vatsimphp/blob/master/examples/easy_api_examples.php).


## Advanced usage

- [Custom logger example](https://github.com/skymeyer/Vatsimphp/blob/master/examples/custom_logger.php)
