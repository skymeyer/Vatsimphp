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
                $this->assertSame($values, array_values($rs->$section->toArray()));
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
                "update": "20201227085129",
                "update_timestamp": "2020-12-27T08:51:29.2145727Z",
                "connected_clients": 649,
                "unique_users": 622
            },
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
                [
                    'clients' => [
                        [
                            'callsign' => 'SWA3437',
                            'cid'      => '123456',
                            'realname' => 'Jelle Vink KSJC',
                            'clienttype' => 'PILOT',
                        ],
                        [
                            'callsign' => 'CPA676',
                            'cid'      => '654321',
                            'realname' => 'John Doe',
                            'clienttype' => 'ATC',
                        ],
                    ],
                    'servers' => [
                        [
                            'ident' => 'CANADA',
                            'hostname_or_IP' => '1.2.3.4',
                            'location'       => 'Toronto, Canada',
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
}
