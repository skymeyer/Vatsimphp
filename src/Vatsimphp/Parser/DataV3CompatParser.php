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

namespace Vatsimphp\Parser;

use Vatsimphp\VatsimData;
use Vatsimphp\Filter\Iterator;

/**
 * Parser for vatsim-data.json using backwards compatible Results. This is a
 * drop-in replacement which is not expecting to affect any existing consumers.
 */
class DataV3CompatParser extends DataParser
{
    /**
     *  JSON decoded data array.
     *
     * @var array
     */
    protected $json = [];

    /**
     * Flight plan legacy field to v3 mapping.
     *
     * @var array
     */
    protected $flightPlanMap = [
        'planned_aircraft' => 'aircraft',
        'planned_tascruise' => 'cruise_tas',
        'planned_depairport' => 'departure',
        'planned_altitude' => 'altitude',
        'planned_destairport' => 'arrival',
        'planned_flighttype' => 'flight_rules',
        'planned_deptime' => 'deptime',
        'planned_hrsenroute' => 'enroute_time',
        'planned_hrsfuel' => 'fuel_time',
        'planned_altairport' => 'alternate',
        'planned_remarks' => 'remarks',
        'planned_route' => 'route',
    ];

    /**
     * Prefile legacy field to v3 mapping.
     *
     * @var array
     */
    protected $prefileMap = [
        'callsign' => 'callsign',
        'cid' => 'cid',
        'realname' => 'name',
    ];

    /**
     * Pilot legacy field to v3 mapping.
     *
     * @var array
     */
    protected $pilotMap = [
        'callsign' => 'callsign',
        'cid' => 'cid',
        'realname' => 'name',
        'latitude' => 'latitude',
        'longitude' => 'longitude',
        'altitude' => 'altitude',
        'groundspeed' => 'groundspeed',
        'server' => 'server',
        'rating' => 'pilot_rating',
        'transponder' => 'transponder',
        'time_logon' => 'logon_time',
        'heading' => 'heading',
        'QNH_iHg' => 'qnh_i_hg',
        'QNH_Mb' => 'qnh_mb',
    ];

    /**
     * Controller legacy field to v3 mapping.
     *
     * @var array
     */
    protected $controllerMap = [
        'callsign' => 'callsign',
        'cid' => 'cid',
        'realname' => 'name',
        'frequency' => 'frequency',
        'server' => 'server',
        'rating' => 'rating',
        'facilitytype' => 'facility',
        'visualrange' => 'visual_range',
        'atis_message' => 'text_atis',
        'time_logon' => 'logon_time',
        'time_last_atis_received' => 'last_updated',
    ];

    /**
     * Server legacy field to v3 mapping.
     *
     * @var array
     */
    protected $serverMap = [
        'ident' => 'ident',
        'hostname_or_IP' => 'hostname_or_ip',
        'location' => 'location',
        'name' => 'name',
        'clients_connection_allowed' => 'clients_connection_allowed',
    ];

    /**
     * List of time fields which require legacy convertion.
     *
     * @var array
     */
    protected $timeFields = [
        'logon_time',
        'last_updated',
    ];

    /**
     * Ctor.
     */
    public function __construct()
    {
        parent::__construct();

        // Add additional general section fields as they may be useful.
        $this->general['update_timestamp'] = false;
        $this->general['unique_users'] = false;
        unset($this->general['atis_allow_min']);
    }

    /**
     * @see Vatsimphp\Parser.ParserInterface::parseData()
     */
    public function parseData()
    {
        $this->json = json_decode(implode("\n", $this->rawData), true, 5);
        parent::parseData();
    }

    /**
     * @see Vatsimphp\Parser.DataParser::parseSections()
     */
    protected function parseSections()
    {
        // Legacy clients section are split off into pilots and controllers.
        // We combined them together as such setting 'clienttype' to honor
        // VatimData query capabilties.

        $this->results->append("clients_header", $this->sectionsHeaders['clients']);
        $clients = [];

        if (isset($this->json['pilots']) && is_array($this->json['pilots'])) {
            foreach ($this->json['pilots'] as $in) {
                $pilot = $this->convertToLegacy($in, $this->pilotMap, $this->getSectionDefault("clients"));
                if (isset($in['flight_plan']) && is_array($in['flight_plan'])) {
                    $pilot = $this->convertToLegacy($in['flight_plan'], $this->flightPlanMap, $pilot);
                }
                $pilot[VatsimData::HEADER_CLIENT_TYPE] = VatsimData::CLIENT_TYPE_PILOT;
                $clients[] = $pilot;
            }
        }

        if (isset($this->json['controllers']) && is_array($this->json['controllers'])) {
            foreach ($this->json['controllers'] as $in) {
                $controller = $this->convertToLegacy($in, $this->controllerMap, $this->getSectionDefault("clients"));
                $controller[VatsimData::HEADER_CLIENT_TYPE] = VatsimData::CLIENT_TYPE_ATC;
                $clients[] = $controller;
            }
        }

        $this->results->append('clients', new Iterator($clients));


        // Prefile section parsing

        $this->results->append("prefile_header", $this->sectionsHeaders['prefile']);
        $prefiles = [];

        if (isset($this->json['prefiles']) && is_array($this->json['prefiles'])) {
            foreach ($this->json['prefiles'] as $in) {
                $prefile = $this->convertToLegacy($in, $this->prefileMap, $this->getSectionDefault("prefile"));
                if (isset($in['flight_plan']) && is_array($in['flight_plan'])) {
                    $prefile = $this->convertToLegacy($in['flight_plan'], $this->flightPlanMap, $prefile);
                }
                $prefiles[] = $prefile;
            }
        }

        $this->results->append('prefile', new Iterator($prefiles));

        // Server section parsing

        $this->results->append("servers_header", $this->sectionsHeaders['servers']);
        $servers = [];

        if (isset($this->json['servers']) && is_array($this->json['servers'])) {
            foreach ($this->json['servers'] as $in) {
                $servers[] = $this->convertToLegacy($in, $this->serverMap, $this->getSectionDefault("servers"));
            }
        }

        $this->results->append('servers', new Iterator($servers));
    }

    /**
     * @see Vatsimphp\Parser.DataParser::parseGeneral()
     */
    protected function parseGeneral()
    {
        if (!isset($this->json["general"])) {
            $this->log->debug("General section not found");
            return;
        }

        foreach ($this->json["general"] as $genKey => $val) {
            if (isset($this->general[$genKey]) && !is_array($val)) {
                $genVal = sprintf("%s", $val);
                $this->general[$genKey] = $genVal;
                $this->log->debug("General section: $genKey -> $genVal");
            } else {
                $this->log->debug("General section: skipping $genKey");
            }
        }

        // convert date to unix timestamp
        $this->general['update'] = $this->convertTs($this->general['update']);
        $this->results->append('general', $this->general);
    }

    /**
     * Convert incoming $data array into legacy format as per supplied map.
     *
     * @param array $data Array container key/value input
     * @param array $map  The transform map legacy -> new
     * @param array $base A base array to apply the transformation on
     *
     * @return array
     */
    protected function convertToLegacy($data, $map, $base = []) {
        foreach ($map as $legacy => $field) {
            if (isset($data[$field])) {
                $base[$legacy] = $this->massageValue($field, $data[$field]);
            }
        }
        return $base;
    }

    /**
     * Massage field values for given new field into legacy format.
     *
     * @param string $field The new field
     * @param mixed $value The new value
     *
     * @return string The legacy value
     */
    protected function massageValue($field, $value) {

        // Arrays are converted to space delimited strings
        if (is_array($value)) {
            $value = implode(" ", $value);
        }

        // Ensure value is a string
        $value = sprintf("%s", $value);

        // Convert time into legacy format
        if (in_array($field, $this->timeFields)) {
            $value = $this->convertISO8601ToLegacy($value);
        }

        return $value;
    }

    /**
     * ISO8601 timestamp convertor to unix time.
     *
     * @param string $str The ISO8601 time representation
     *
     * @return int
     */
    protected function convertISO8601ToLegacy($str)
    {
        $dt = new \DateTime($str);
        $dt->setTimezone(new \DateTimeZone('UTC'));
        return $dt->format("YmdHis");
    }

    /**
     * Return section default skeleton/values.
     *
     * @param string $section The section name as defined in $this->sectionHeaders
     *
     * @return array
     */
    protected function getSectionDefault($section) {
        $default = [];
        if (isset($this->sectionsHeaders[$section])) {
            foreach ($this->sectionsHeaders[$section] as $header) {
                // Setting empty string to avoid having per field defaults which is
                // is out of scope of the compatibility.
                $default[$header] = "";
            }
        }
        return $default;
    }
}
