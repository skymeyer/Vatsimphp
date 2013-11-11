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
 * https://github.com/skymeyer/Vatsimphp/blob/master/docs/index.md
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
