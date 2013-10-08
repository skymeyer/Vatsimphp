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

namespace Vatsimphp;

class MetarParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Parser\MetarParser')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Parser\AbstractParser', $class);
    }

    /**
     *
     * Parse data test
     * @dataProvider providerTestParseData
     */
    public function testParseData($data, $expectedStatus, $expectedData)
    {
        $parser = $this->getMockParser('Metar');
        $parser->setData($data);
        $parser->parseData();
        $this->assertSame($expectedStatus, $parser->isValid());

        if ($expectedStatus) {
            $rs = $parser->getParsedData()->metar->toArray(false);
            $this->assertSame($expectedData, $rs[0]);
        }
    }

    public function providerTestParseData()
    {
        $validData1 = "KSFO 071056Z 00000KT 10SM SCT150 15/M01 A2997 RMK AO2 SLP148 T01501006 $";
        $validData2 = "\n{$validData1}\n";
        $invalidData1 = "No METAR available for ZZZZ";
        $invalidData2 = "\n{$invalidData1}\n";
        return array(
            array($validData1, true, $validData1),
            array($validData2, true, $validData1),
            array($invalidData1, false, ''),
            array($invalidData2, false, ''),
        );
    }

    /**
     *
     * Mock parser object with silent Logger
     */
    protected function getMockParser($name)
    {
        $class = $this->getMockBuilder('Vatsimphp\Parser\\'.$name.'Parser')
            ->setMethods(null)
            ->getMock();
        return $class;
    }
}
