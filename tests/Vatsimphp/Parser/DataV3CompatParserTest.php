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

namespace Vatsimphp;

use PHPUnit\Framework\TestCase;

class DataV3CompatParserTest extends TestCase
{
    /**
     * Test inheritance.
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Parser\DataV3CompatParser')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Parser\AbstractParser', $class);
    }

    /**
     * Parse data test.
     *
     * @dataProvider providerTestParseData
     * @covers Vatsimphp\Parser\DataV3CompatParser::parseData
     * @covers Vatsimphp\Parser\DataV3CompatParser::parseSections
     * @covers Vatsimphp\Parser\DataV3CompatParser::parseGeneral
     */
    public function testParseData($data, $expectedStatus, $expectedData, $expire = 0)
    {
        $class = $this->getMockParser('DataV3Compat');

        // parse data
        $class->dataExpire = $expire;
        $class->setData($data);
        $class->parseData();
        $this->assertSame($expectedStatus, $class->isValid());

        // additional test if success is expected
        if ($expectedStatus) {

            // all general values should not be false
            $genProp = new \ReflectionProperty($class, 'general');
            $genProp->setAccessible(true);
            foreach ($genProp->getValue($class) as $value) {
                $this->assertNotEmpty($value);
            }

            // test actual data
            $rs = $class->getParsedData();
            foreach ($expectedData as $section => $values) {
                $this->assertEqualsArray($values, array_values($rs->$section->toArray()));
            }
        }
    }

    public function providerTestParseData()
    {
        // valid (normal) data
        $validData = '{
            "general": {
                "version": 3,
                "reload": 1,
                "update": "20210101223245",
                "update_timestamp": "2021-01-01T22:32:45.2145727Z",
                "connected_clients": 649,
                "unique_users": 622
            },
            "pilots": [
                {
                    "cid": 1234567,
                    "name": "John Doe",
                    "callsign": "SWA3437",
                    "server": "USA-WEST",
                    "pilot_rating": 1,
                    "latitude": 34.57682,
                    "longitude": -116.04695,
                    "altitude": 29273,
                    "groundspeed": 467,
                    "transponder": "3262",
                    "heading": 253,
                    "qnh_i_hg": 30.18,
                    "qnh_mb": 1022,
                    "flight_plan":{
                        "flight_rules": "I",
                        "aircraft": "H/CONC/L",
                        "departure": "KORD",
                        "arrival": "KLAX",
                        "alternate": "KONT",
                        "cruise_tas": "483",
                        "altitude": "50000",
                        "deptime": "0",
                        "enroute_time": "0",
                        "fuel_time": "0",
                        "remarks": "A/C Type  Concorde.  RNAV and RVSM equipped. /v/",
                        "route": "+PEKUE PIPPN ROTTN PWE J64 TBC JASSE Q90 DNERO ANJLL4"
                    },
                    "logon_time":"2021-01-01T20:10:13.1294438Z",
                    "last_updated":"2021-01-02T00:17:15.664797Z"
                },
                {
                    "cid": 99887766,
                    "name": "John Doe II",
                    "callsign": "SWA3436",
                    "server": "USA-WEST",
                    "pilot_rating": 1,
                    "latitude": 35.57682,
                    "longitude": -115.04695,
                    "altitude": 29273,
                    "groundspeed": 467,
                    "transponder": "3262",
                    "heading": 253,
                    "qnh_i_hg": 30.18,
                    "qnh_mb": 1022,
                    "logon_time":"2021-01-01T20:10:13.1294438Z",
                    "last_updated":"2021-01-02T00:17:15.664797Z"
                }
            ],
            "controllers": [
                {
                    "cid": 7654321,
                    "name": "John Foe",
                    "callsign": "KBOS_ATIS",
                    "frequency": "135.000",
                    "facility": 4,
                    "rating": 3,
                    "server": "USA-WEST",
                    "visual_range": 50,
                    "text_atis":[
                        "BOSTON LOGAN AIRPORT ATIS INFORMATION Z. 2254Z. 15005KT 10SM",
                        "FEW120 BKN160 OVC250 01/-03 A3040. ILS RWY 22L APCH IN USE, DEPTG RWY 22R."
                    ],
                    "last_updated":"2021-01-02T20:28:38.2242002Z",
                    "logon_time":"2021-01-01T20:28:38.1658435Z"
                }
            ],
            "prefiles": [
                {
                    "cid": 11223344556677,
                    "name": "Jane Doe",
                    "callsign": "SWA3438",
                    "flight_plan":{
                        "flight_rules": "I",
                        "aircraft": "A21N/L",
                        "departure":"KCLT",
                        "arrival": "KBOS",
                        "alternate": "KJFK",
                        "cruise_tas": "449",
                        "altitude": "37000",
                        "deptime": "2035",
                        "enroute_time": "0129",
                        "fuel_time": "0336",
                        "remarks": "SEL/FMLP PER/C TALT/KATL RMK/TCAS /V/",
                        "route": "BARMY4 RDU THHMP OOD J42 RBV J222 JFK ROBUC3"
                    },
                    "last_updated": "2021-01-01T21:26:52.3657456Z"
                }
            ],
            "servers": [
                {
                    "ident": "CANADA",
                    "hostname_or_ip": "165.22.239.218",
                    "location": "Toronto, Canada",
                    "name": "CANADA",
                    "clients_connection_allowed": 1
                }
            ]
        }';

        $expectedValidData = [
            'clients' => [
                [
                    'callsign' => 'SWA3437',
                    'cid'      => '1234567',
                    'realname' => 'John Doe',
                    'clienttype' => 'PILOT',
                    'frequency' => '',
                    'latitude' => '34.57682',
                    'longitude' => '-116.04695',
                    'altitude' => '29273',
                    'groundspeed' => '467',
                    'planned_aircraft' => 'H/CONC/L',
                    'planned_tascruise' => '483',
                    'planned_depairport' => 'KORD',
                    'planned_altitude' => '50000',
                    'planned_destairport' => 'KLAX',
                    'server' => 'USA-WEST',
                    'protrevision' => '', // 100
                    'rating' => '1',
                    'transponder' => '3262',
                    'facilitytype' => '', // 0
                    'visualrange' => '', // 0
                    'planned_revision' => '', // 4
                    'planned_flighttype' => 'I',
                    'planned_deptime' => '0',
                    'planned_actdeptime' => '', // 0
                    'planned_hrsenroute' => '0',
                    'planned_minenroute' => '', // 0
                    'planned_hrsfuel' => '0',
                    'planned_minfuel' => '', // 0
                    'planned_altairport' => 'KONT',
                    'planned_remarks' => 'A/C Type  Concorde.  RNAV and RVSM equipped. /v/',
                    'planned_route' => '+PEKUE PIPPN ROTTN PWE J64 TBC JASSE Q90 DNERO ANJLL4',
                    'planned_depairport_lat' => '', // 0
                    'planned_depairport_lon' => '', // 0
                    'planned_destairport_lat' => '', // 0
                    'planned_destairport_lon' => '', // 0
                    'atis_message' => '',
                    'time_last_atis_received' => '', // 20210101201013
                    'time_logon' => '20210101201013',
                    'heading' => '253',
                    'QNH_iHg' => '30.18',
                    'QNH_Mb' => '1022',
                ],
                [
                    'callsign' => 'SWA3436',
                    'cid'      => '99887766',
                    'realname' => 'John Doe II',
                    'clienttype' => 'PILOT',
                    'frequency' => '',
                    'latitude' => '35.57682',
                    'longitude' => '-115.04695',
                    'altitude' => '29273',
                    'groundspeed' => '467',
                    'planned_aircraft' => '',
                    'planned_tascruise' => '',
                    'planned_depairport' => '',
                    'planned_altitude' => '',
                    'planned_destairport' => '',
                    'server' => 'USA-WEST',
                    'protrevision' => '', // 100
                    'rating' => '1',
                    'transponder' => '3262',
                    'facilitytype' => '', // 0
                    'visualrange' => '', // 0
                    'planned_revision' => '',
                    'planned_flighttype' => '',
                    'planned_deptime' => '',
                    'planned_actdeptime' => '',
                    'planned_hrsenroute' => '',
                    'planned_minenroute' => '',
                    'planned_hrsfuel' => '',
                    'planned_minfuel' => '',
                    'planned_altairport' => '',
                    'planned_remarks' => '',
                    'planned_route' => '',
                    'planned_depairport_lat' => '',
                    'planned_depairport_lon' => '',
                    'planned_destairport_lat' => '',
                    'planned_destairport_lon' => '',
                    'atis_message' => '',
                    'time_last_atis_received' => '', // 20210101201013
                    'time_logon' => '20210101201013',
                    'heading' => '253',
                    'QNH_iHg' => '30.18',
                    'QNH_Mb' => '1022',
                ],
                [
                    'callsign' => 'KBOS_ATIS',
                    'cid' => '7654321',
                    'realname' => 'John Foe',
                    'clienttype' => 'ATC',
                    'frequency' => '135.000',
                    'latitude' => '', // 42.36296
                    'longitude' => '', // -71.00643
                    'altitude' => '', // 0
                    'groundspeed' => '', // 0
                    'planned_aircraft' => '',
                    'planned_tascruise' => '',
                    'planned_depairport' => '',
                    'planned_altitude' => '',
                    'planned_destairport' => '',
                    'server' => 'USA-WEST',
                    'protrevision' => '', // 100
                    'rating' => '3',
                    'transponder' => '', // 0
                    'facilitytype' => '4',
                    'visualrange' => '50',
                    'planned_revision' => '',
                    'planned_flighttype' => '',
                    'planned_deptime' => '',
                    'planned_actdeptime' => '',
                    'planned_hrsenroute' => '',
                    'planned_minenroute' => '',
                    'planned_hrsfuel' => '',
                    'planned_minfuel' => '',
                    'planned_altairport' => '',
                    'planned_remarks' => '',
                    'planned_route' => '',
                    'planned_depairport_lat' => '', // 0
                    'planned_depairport_lon' => '', // 0
                    'planned_destairport_lat' => '', // 0
                    'planned_destairport_lon' => '', // 0
                    'atis_message' => 'BOSTON LOGAN AIRPORT ATIS INFORMATION Z. 2254Z. 15005KT 10SM FEW120 BKN160 OVC250 01/-03 A3040. ILS RWY 22L APCH IN USE, DEPTG RWY 22R.',
                    'time_last_atis_received' => '20210102202838',
                    'time_logon' => '20210101202838',
                    'heading' => '', // 0
                    'QNH_iHg' => '', // 0
                    'QNH_Mb' => '', // 0
                ],
            ],
            'prefile' => [
                [
                    'callsign' => 'SWA3438',
                    'cid' => '11223344556677',
                    'realname' => 'Jane Doe',
                    'clienttype' => '',
                    'frequency' => '',
                    'latitude' => '', // 0
                    'longitude' => '', // 0
                    'altitude' => '', // 0
                    'groundspeed' => '', // 0
                    'planned_aircraft' => 'A21N/L',
                    'planned_tascruise' => '449',
                    'planned_depairport' => 'KCLT',
                    'planned_altitude' => '37000',
                    'planned_destairport' => 'KBOS',
                    'server' => '',
                    'protrevision' => '', // 0
                    'rating' => '', // 0
                    'transponder' => '', // 0
                    'facilitytype' => '', // 0
                    'visualrange' => '', // 0
                    'planned_revision' => '', // 3
                    'planned_flighttype' => 'I',
                    'planned_deptime' => '2035',
                    'planned_actdeptime' => '', // 2035
                    'planned_hrsenroute' => '0129', // needs parsing
                    'planned_minenroute' => '', // 29
                    'planned_hrsfuel' => '0336', // needs parsing
                    'planned_minfuel' => '', // 36
                    'planned_altairport' => 'KJFK',
                    'planned_remarks' => 'SEL/FMLP PER/C TALT/KATL RMK/TCAS /V/',
                    'planned_route' => 'BARMY4 RDU THHMP OOD J42 RBV J222 JFK ROBUC3',
                    'planned_depairport_lat' => '', // 0
                    'planned_depairport_lon' => '', // 0
                    'planned_destairport_lat' => '', // 0
                    'planned_destairport_lon' => '', // 0
                    'atis_message' => '',
                    'time_last_atis_received' => '', // 00010101000000
                    'time_logon' => '', // 00010101000000
                    'heading' => '', // 0
                    'QNH_iHg' => '', // 0
                    'QNH_Mb' => '', // 0
                ],
            ],
            'servers' => [
                [
                    'ident' => 'CANADA',
                    'hostname_or_IP' => '165.22.239.218',
                    'location' => 'Toronto, Canada',
                    'name' => 'CANADA',
                    'clients_connection_allowed' => '1',
                ],
            ],
        ];

        // without general section
        $invalidData1 = '{
            "pilots": [
                {
                    "cid": 123456,
                    "name": "Jelle Vink KSJC",
                    "callsign": "SWA3437"
                }
            ],
            "controllers": [
                {
                    "cid": 654321,
                    "name": "John Doe",
                    "callsign": "CPA676"
                }
            ],
            "servers": [
                {
                    "ident": "CANADA",
                    "hostname_or_ip": "1.2.3.4",
                    "location": "Toronto, Canada"
                }
            ]
        }';

        return [
            [
                $validData,
                true,
                $expectedValidData,
            ],
            [
                $invalidData1,
                false,
                [],
            ],
            [
                $validData,
                false,
                [],
                300,
            ],
        ];
    }

    /**
     * Mock parser object.
     */
    protected function getMockParser($name)
    {
        $class = $this->getMockBuilder('Vatsimphp\Parser\\'.$name.'Parser')
            ->setMethods(null)
            ->getMock();

        return $class;
    }

    /**
     * Helper comparing to keyed arrays.
     */
    protected function assertEqualsArray($exp, $act) {
        ksort($exp);
        ksort($act);
        $this->assertEquals($exp, $act);
    }
}
