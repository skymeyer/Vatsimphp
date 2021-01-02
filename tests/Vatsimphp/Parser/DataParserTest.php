<?php

/*
 * This file is part of the Vatsimphp package
 *
 * Copyright 2018 - Jelle Vink <jelle.vink@gmail.com>
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

class DataParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test inheritance.
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Parser\DataParser')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Parser\AbstractParser', $class);
    }

    /**
     * Parse data test.
     *
     * @dataProvider providerTestParseData
     * @covers Vatsimphp\Parser\DataParser::parseData
     * @covers Vatsimphp\Parser\DataParser::parseSections
     * @covers Vatsimphp\Parser\DataParser::parseGeneral
     * @covers Vatsimphp\Parser\AbstractParser::getParsedData
     * @covers Vatsimphp\Result\ResultContainer::append
     * @covers Vatsimphp\Result\ResultContainer::getList
     */
    public function testParseData($data, $expectedStatus, $expectedData, $expire = 0)
    {
        $class = $this->getMockParser('Data');

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
                $this->assertEquals($values, array_values($rs->$section->toArray()));
            }
        }
    }

    public function providerTestParseData()
    {
        $general = "!GENERAL:\n";
        $general .= "VERSION = 1\nRELOAD = 2\nUPDATE = 20210101223245\n";
        $general .= "ATIS ALLOW MIN = 5\nCONNECTED CLIENTS = 649\nUNIQUE USERS = 622\n";

        $generalInvalid = "!GENERAL:\n";

        $clientsHead = "; !CLIENTS section - callsign:cid:realname:clienttype:frequency:latitude:longitude:altitude:groundspeed:planned_aircraft:planned_tascruise:planned_depairport:planned_altitude:planned_destairport:server:protrevision:rating:transponder:facilitytype:visualrange:planned_revision:planned_flighttype:planned_deptime:planned_actdeptime:planned_hrsenroute:planned_minenroute:planned_hrsfuel:planned_minfuel:planned_altairport:planned_remarks:planned_route:planned_depairport_lat:planned_depairport_lon:planned_destairport_lat:planned_destairport_lon:atis_message:time_last_atis_received:time_logon:heading:QNH_iHg:QNH_Mb:\n";
        $clients = "!CLIENTS:\n";
        $clients .= "SWA3437:1234567:John Doe:PILOT::34.57682:-116.04695:29273:467:H/CONC/L:483:KORD:50000:KLAX:USA-WEST:100:1:3262:0:0:4:I:0:0:0:0:0:0:KONT:A/C Type  Concorde.  RNAV and RVSM equipped. /v/:+PEKUE PIPPN ROTTN PWE J64 TBC JASSE Q90 DNERO ANJLL4:0:0:0:0::20210101201013:20210101201013:253:30.18:1022:\n";
        $clients .= "KBOS_ATIS:7654321:John Foe:ATC:135.000:42.36296:-71.00643:0:0::::::USA-WEST:100:3:0:4:50::::::::::::0:0:0:0:BOSTON LOGAN AIRPORT ATIS INFORMATION Z. 2254Z. 15005KT 10SM FEW120 BKN160 OVC250 01/-03 A3040. ILS RWY 22L APCH IN USE, DEPTG RWY 22R.:20210101202838:20210101202838:0:0:0:\n";

        $prefile = "!PREFILE:\n";
        $prefile .= "SWA3438:11223344556677:Jane Doe:::0:0:0:0:A21N/L:449:KCLT:37000:KBOS::0:0:0:0:0:3:I:2035:2035:1:29:3:36:KJFK:SEL/FMLP PER/C TALT/KATL RMK/TCAS /V/:BARMY4 RDU THHMP OOD J42 RBV J222 JFK ROBUC3:0:0:0:0::00010101000000:00010101000000:0:0:0:\n";

        $serversHead = "; !VOICE SERVERS section -   hostname_or_IP:location:\n";
        $servers = "!SERVERS:\n";
        $servers .= "CANADA:165.22.239.218:Toronto, Canada:CANADA:1:\n";

        // valid (normal) data
        $validData1 = $general.$clientsHead.$clients.$prefile.$serversHead.$servers;

        // valid without voice head
        $validData2 = $general.$clientsHead.$clients.$prefile.$servers;

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
                    'protrevision' => '100',
                    'rating' => '1',
                    'transponder' => '3262',
                    'facilitytype' => '0',
                    'visualrange' => '0',
                    'planned_revision' => '4',
                    'planned_flighttype' => 'I',
                    'planned_deptime' => '0',
                    'planned_actdeptime' => '0',
                    'planned_hrsenroute' => '0',
                    'planned_minenroute' => '0',
                    'planned_hrsfuel' => '0',
                    'planned_minfuel' => '0',
                    'planned_altairport' => 'KONT',
                    'planned_remarks' => 'A/C Type  Concorde.  RNAV and RVSM equipped. /v/',
                    'planned_route' => '+PEKUE PIPPN ROTTN PWE J64 TBC JASSE Q90 DNERO ANJLL4',
                    'planned_depairport_lat' => '0',
                    'planned_depairport_lon' => '0',
                    'planned_destairport_lat' => '0',
                    'planned_destairport_lon' => '0',
                    'atis_message' => '',
                    'time_last_atis_received' => '20210101201013',
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
                    'latitude' => '42.36296',
                    'longitude' => '-71.00643',
                    'altitude' => '0',
                    'groundspeed' => '0',
                    'planned_aircraft' => '',
                    'planned_tascruise' => '',
                    'planned_depairport' => '',
                    'planned_altitude' => '',
                    'planned_destairport' => '',
                    'server' => 'USA-WEST',
                    'protrevision' => '100',
                    'rating' => '3',
                    'transponder' => '0',
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
                    'planned_depairport_lat' => '0',
                    'planned_depairport_lon' => '0',
                    'planned_destairport_lat' => '0',
                    'planned_destairport_lon' => '0',
                    'atis_message' => 'BOSTON LOGAN AIRPORT ATIS INFORMATION Z. 2254Z. 15005KT 10SM FEW120 BKN160 OVC250 01/-03 A3040. ILS RWY 22L APCH IN USE, DEPTG RWY 22R.',
                    'time_last_atis_received' => '20210101202838',
                    'time_logon' => '20210101202838',
                    'heading' => '0',
                    'QNH_iHg' => '0',
                    'QNH_Mb' => '0',
                ],
            ],
            'prefile' => [
                [
                    'callsign' => 'SWA3438',
                    'cid' => '11223344556677',
                    'realname' => 'Jane Doe',
                    'clienttype' => '',
                    'frequency' => '',
                    'latitude' => '0',
                    'longitude' => '0',
                    'altitude' => '0',
                    'groundspeed' => '0',
                    'planned_aircraft' => 'A21N/L',
                    'planned_tascruise' => '449',
                    'planned_depairport' => 'KCLT',
                    'planned_altitude' => '37000',
                    'planned_destairport' => 'KBOS',
                    'server' => '',
                    'protrevision' => '0',
                    'rating' => '0',
                    'transponder' => '0',
                    'facilitytype' => '0',
                    'visualrange' => '0',
                    'planned_revision' => '3',
                    'planned_flighttype' => 'I',
                    'planned_deptime' => '2035',
                    'planned_actdeptime' => '2035',
                    'planned_hrsenroute' => '1',
                    'planned_minenroute' => '29',
                    'planned_hrsfuel' => '3',
                    'planned_minfuel' => '36',
                    'planned_altairport' => 'KJFK',
                    'planned_remarks' => 'SEL/FMLP PER/C TALT/KATL RMK/TCAS /V/',
                    'planned_route' => 'BARMY4 RDU THHMP OOD J42 RBV J222 JFK ROBUC3',
                    'planned_depairport_lat' => '0',
                    'planned_depairport_lon' => '0',
                    'planned_destairport_lat' => '0',
                    'planned_destairport_lon' => '0',
                    'atis_message' => '',
                    'time_last_atis_received' => '00010101000000',
                    'time_logon' => '00010101000000',
                    'heading' => '0',
                    'QNH_iHg' => '0',
                    'QNH_Mb' => '0',
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
        $invalidData1 = $clientsHead.$clients.$serversHead.$servers;

        // with invalid general section
        $invalidData2 = $generalInvalid.$clientsHead.$clients.$serversHead.$servers;

        return [
            [
                $validData1,
                true,
                $expectedValidData,
            ],
            [
                $validData2,
                true,
                $expectedValidData,
            ],
            [
                $invalidData1,
                false,
                [],
            ],
            [
                $invalidData2,
                false,
                [],
            ],
            [
                $validData1,
                false,
                [],
                300,
            ],
        ];
    }

    /**
     * Test scrub key.
     *
     * @dataProvider providerTestScrubKey
     * @covers Vatsimphp\Parser\DataParser::scrubKey
     */
    public function testScrubKey($input, $expectedOutput)
    {
        $class = $this->getMockParser('Data');
        $scrub = new \ReflectionMethod($class, 'scrubKey');
        $scrub->setAccessible(true);
        $this->assertSame($expectedOutput, $scrub->invoke($class, $input));
    }

    public function providerTestScrubKey()
    {
        return [
            ['voice server', 'voice_server'],
            ['VoIcE SeRvEr', 'voice_server'],
        ];
    }

    /**
     * Test convert timestamp.
     *
     * @dataProvider providerTestConvertTs
     * @covers Vatsimphp\Parser\DataParser::convertTs
     */
    public function testConvertTs($input, $expectedOutput)
    {
        $class = $this->getMockParser('Data');
        $scrub = new \ReflectionMethod($class, 'convertTs');
        $scrub->setAccessible(true);
        $this->assertSame($expectedOutput, $scrub->invoke($class, $input));
    }

    public function providerTestConvertTs()
    {
        return [
            ['20130601000000', 1370044800],
            ['', false],
            ['123456789012345', false],
        ];
    }

    /**
     * Test timestamp expire.
     *
     * @dataProvider providerTestTimestampHasExpired
     * @covers Vatsimphp\Parser\DataParser::timestampHasExpired
     */
    public function testTimestampHasExpired($ts, $expire, $result)
    {
        $class = $this->getMockParser('Data');
        $expired = new \ReflectionMethod($class, 'timestampHasExpired');
        $expired->setAccessible(true);
        $test = $expired->invoke($class, $ts, $expire);
        $this->assertSame($result, $test);
    }

    public function providerTestTimestampHasExpired()
    {
        return [
            [
                time() - 9999,
                30,
                true,
            ],
            [
                time() - 99,
                300,
                false,
            ],
            [
                time() - 9999,
                0,
                false,
            ],
            [
                time() + 9999,
                0,
                false,
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
}
