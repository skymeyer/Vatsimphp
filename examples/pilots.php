<?php

/*
 * This file is part of the Vatsimphp package
 *
 * Copyright 2020 - Jelle Vink <jelle.vink@gmail.com>
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
 */

require_once 'vendor/autoload.php';

/**
 * Consult the documentation for more details at:
 * https://github.com/skymeyer/Vatsimphp/blob/master/docs/index.md
 **/

$callSign = 'BAW';

$vatsim = new \Vatsimphp\VatsimData();
$vatsim->setConfig('cacheOnly', true);

if ($vatsim->loadData()) {
    $pilots = $vatsim->searchCallsign($callSign);
    foreach ($pilots as $pilot) {
        echo "{$pilot['callsign']} => {$pilot['realname']}\n";
    }
} else {
    echo "Data could not be loaded \n";
}
