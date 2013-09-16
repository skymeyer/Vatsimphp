<?php

/*
 * Example: interactive_search_controller
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

// use global search interface - no direct call exposed for now
$query = $vatsim->search('clients', array('clienttype' => 'ATC'));
if (!count($query)) {
    echo "************************************************************" . PHP_EOL;
    echo "No controllers online with this callsign !" . PHP_EOL;
}
foreach ($query as $result) {
    echo "************************************************************" . PHP_EOL;
    echo "Callsign  -> " . $result['callsign'] . PHP_EOL;
    echo "Real name -> " . $result['realname'] . PHP_EOL;
    echo "Vatsim ID -> " . $result['cid'] . PHP_EOL;
    echo "Vatsim ID -> " . $result['frequency'] . PHP_EOL;
    // var_dump($result);
}
