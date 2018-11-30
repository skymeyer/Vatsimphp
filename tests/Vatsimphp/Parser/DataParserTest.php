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

use PHPUnit\Framework\TestCase;

class DataParserTest extends TestCase
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
                $this->assertSame($values, array_values($rs->$section->toArray()));
            }
        }
    }

    public function providerTestParseData()
    {
        $general = "!GENERAL:\n";
        $general .= "VERSION = 1\nRELOAD = 2\nUPDATE = 19990601000000\n";
        $general .= "ATIS ALLOW MIN = 5\nCONNECTED CLIENTS = 1";

        $generalInvalid = "!GENERAL:\n";
        $generalInvalid .= "VERSION = 1\nRELOAD\n";
        $generalInvalid .= "ATIS ALLOW MIN = 5\nCONNECTED CLIENTS = 1";

        $clientsHead = "\n; !CLIENTS section -         callsign:cid:realname:\n";
        $clients = "!CLIENTS:\n";
        $clients .= 'SWA3437:123456:Jelle Vink KSJC:';

        $voiceHead = "\n; !VOICE SERVERS section -   hostname_or_IP:location:\n";
        $voice = "!VOICE SERVERS:\n";
        $voice .= 'rw.liveatc.net:North America, USA, California:';

        // valid (normal) data
        $validData = $general.$clientsHead.$clients.$voiceHead.$voice;

        // without general section
        $invalidData1 = $clientsHead.$clients.$voiceHead.$voice;

        // with invalid general section
        $invalidData2 = $generalInvalid.$clientsHead.$clients.$voiceHead.$voice;

        return [
            [
                $validData,
                true,
                [
                    'clients' => [
                        [
                            'callsign' => 'SWA3437',
                            'cid'      => '123456',
                            'realname' => 'Jelle Vink KSJC',
                        ],
                    ],
                    'voice_servers' => [
                        [
                            'hostname_or_IP' => 'rw.liveatc.net',
                            'location'       => 'North America, USA, California',
                        ],
                    ],
                ],
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
                $validData,
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
