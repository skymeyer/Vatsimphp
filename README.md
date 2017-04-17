# Vatsimphp [![Build Status](https://img.shields.io/travis/skymeyer/Vatsimphp.svg)](https://travis-ci.org/skymeyer/Vatsimphp) [![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/skymeyer/Vatsimphp.svg)](https://scrutinizer-ci.com/g/skymeyer/Vatsimphp/?branch=master) [![Code Quality](https://img.shields.io/scrutinizer/g/skymeyer/Vatsimphp.svg)](https://scrutinizer-ci.com/g/skymeyer/Vatsimphp/?branch=master)

[![Latest Stable Version](https://img.shields.io/github/release/skymeyer/vatsimphp.svg)](https://packagist.org/packages/skymeyer/vatsimphp)
[![Total downloads](https://img.shields.io/packagist/dt/skymeyer/vatsimphp.svg)](https://packagist.org/packages/skymeyer/vatsimphp)
[![Dependency Status](https://img.shields.io/versioneye/d/php/skymeyer:vatsimphp.svg)](https://www.versioneye.com/php/skymeyer:vatsimphp/dev-master)
[![Join the chat at https://gitter.im/skymeyer/Vatsimphp](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/skymeyer/Vatsimphp)


[![Join the chat at https://gitter.im/skymeyer/Vatsimphp](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/skymeyer/Vatsimphp?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Vatsimphp collects and parses the publically available statistics
from the [VATSIM.net](http://www.vatsim.net) network. It provides
iterators for the available data and basic search/filtering
capabilities without using a database backend.

Vatsimphp uses an intelligent local file cache to avoid consuming
unnecessary bandwidth from the public data servers. The software
comes with sensible default settings, but can be changed if
required. Vatsimphp can be dropped into your crontab very easily
to avoid inline data updates in your web application.

A "cache only" node is available if another process
is already responsible to retrieve the raw data files from the
VATSIM network or if live connections are not applicable.

Vatsimphp can be easily plugged into existing PHP systems to
query VATSIM data or feed the parsed results into a database
backend of your choice.

Documentation
-------------

- [Browse documentation](https://github.com/skymeyer/Vatsimphp/blob/master/docs/index.md)
- [Browse examples](https://github.com/skymeyer/Vatsimphp/tree/master/examples)


About
=====

Requirements
------------

- From PHP 5.3.29 and above
- PHPUnit 3.7 or higher for test suite execution

Bugs and feature requests
-------------------------

Bugs and feature request can be filed on the [issues](https://github.com/skymeyer/Vatsimphp/issues) page.

Contributing
------------

Contributions are welcome in respect of the [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
coding style and unit test coverage.

Changelog
---------

See the `CHANGELOG.md` file for more details.

Author
------

Jelle Vink - <jelle.vink@gmail.com> (<http://skymeyer.be>)

License
-------

Vatsimphp is licensed under the Apache License, Version 2.0. Check the `LICENSE` and `NOTICE` file for details.

Disclaimer
----------

VATSIM (Virtual Air Traffic Simulation Network) is a non-profit organisation.
The development of this software is not directly affiliated to the VATSIM
organisation and provided on an "as is" basis as set forward in the above License.
This software does not connect to the VATSIM network directly, but rather consumes
publically available resources produced by the VATSIM network.

More information on VATSIM can be found at <http://www.vatsim.net>.
