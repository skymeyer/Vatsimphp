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

require_once '../vendor/autoload.php';

$vatsim = new VatsimData();
$vatsim->loadData();

// General vatsim statistics
$general = $vatsim->getGeneralInfo()->toArray();

// List all pilots
$pilots = $vatsim->getPilots()->toArray();

// List all controllers
$controllers = $vatsim->getControllers()->toArray();

// List all clients (pilots and controllers)
$clients = $vatsim->getClients()->toArray();

// List all servers
$servers = $vatsim->getServers()->toArray();

// List all voice servers
$voice = $vatsim->getVoiceServers()->toArray();

// List all prefile registrations
$prefile = $vatsim->getPrefile()->toArray();

// Search clients based on call sign
$aal = $vatsim->searchCallsign('AAL')->toArray();

// Search clients based on vatsim id
$user = $vatsim->searchVatsimId('1165529')->toArray();

// Get the METAR for KSFO
$ksfo = $vatsim->getMetar('KSFO');
