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
if (!$vatsim->loadData()) {
    // handle error - something went wrong !
}

$query = $vatsim->searchCallsign('AAL');
if (!count($query)) {
    echo "************************************************************" . PHP_EOL;
    echo "No pilots online with this callsign !" . PHP_EOL;
}
foreach ($query as $result) {
    echo "************************************************************" . PHP_EOL;
    echo "Callsign  -> " . $result['callsign'] . PHP_EOL;
    echo "Real name -> " . $result['realname'] . PHP_EOL;
    echo "Vatsim ID -> " . $result['cid'] . PHP_EOL;
    echo "Squawk    -> " . $result['transponder'] . PHP_EOL;
    echo "Departure -> " . $result['planned_depairport'] . PHP_EOL;
    echo "Arrival   -> " . $result['planned_destairport'] . PHP_EOL;
    echo "Route     -> " . $result['planned_route'] . PHP_EOL;
    echo "Altitude  -> " . $result['altitude'] . PHP_EOL;
    // var_dump($result);
}
