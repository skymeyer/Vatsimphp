<?php

/*
 * This file is part of the Vatsimphp package
 *
 * Copyright 2013 - Jelle Vink <jelle.vink@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

/**
 * Consult the documentation for more details at:
 * https://github.com/skymeyer/Vatsimphp/tree/master/docs
 **/

use Vatsimphp\VatsimData;
use Vatsimphp\Log\LoggerFactory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once '../vendor/autoload.php';

// Create custom logger based on Monolog (note: every PSR-3 compliant logger will work)
// see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
$logFile = __DIR__ . '/../app/logs/vatsimphp_custom.log';
$logger = new Logger('vatsimphp');
$logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));

// Register custom logger
LoggerFactory::register("_DEFAULT_", $logger);

$vatsim = new VatsimData();
$vatsim->loadData();

// see app/logs/vatsimphp_custom.log for the result
