<?php

/*
 * Example: interactive_search_callsign
 *
 * Downloads necessary statistics from one of the availble
 * data servers if local content is expired.
 *
 * Note: Not advised to use for inline calls of your
 * web application. See cron documentation.
 *
 */

use Vatsimphp\VatsimData;
use Vatsimphp\Log\LoggerFactory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once '../vendor/autoload.php';

// Create custom logger based on Monolog (note: every PSR-3 compliant logger will work)
// see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
$logger = new Logger('vatsimphp');
$logger->pushHandler(new StreamHandler('vatsim.log', Logger::DEBUG));

// Register custom logger
LoggerFactory::register("_DEFAULT_", $logger);

$vatsim = new VatsimData();
$vatsim->loadData();

// see vatsim.log for the result
