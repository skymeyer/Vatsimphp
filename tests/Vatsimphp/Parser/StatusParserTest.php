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

class StatusParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Parser\StatusParser')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Parser\AbstractParser', $class);
    }

    /**
     *
     * Parse data test
     * @dataProvider providerTestParseData
     * @covers Vatsimphp\Parser\StatusParser::parseData
     * @covers Vatsimphp\Parser\AbstractParser::getParsedData
     * @covers Vatsimphp\Result\ResultContainer::append
     * @covers Vatsimphp\Result\ResultContainer::getList
     */
    public function testParseData($data, $expectedStatus, $expectedData)
    {
        $class = $this->getMockParser('Status');

        // parse data
        $class->setData($data);
        $class->parseData();
        $this->assertSame($expectedStatus, $class->isValid());

        // compare list of results
        $endProp = new \ReflectionProperty($class, 'endpoints');
        $endProp->setAccessible(true);
        $this->assertSame(
            array_values($endProp->getValue($class)),
            $class->getParsedData()->getList()
        );

        // compare result values
        if ($expectedStatus) {
            $rs = $class->getParsedData();
            foreach ($expectedData as $endpoint => $values) {
                $this->assertSame($values, array_values($rs->$endpoint->toArray()));
            }
        }
    }

    public function providerTestParseData()
    {
        return array(
            array(
                "url0=aaa\nurl0=bbb\nurl1=ccc\nmetar0=ddd\natis0=eee",
                true,
                array(
                    'dataUrls' => array('aaa', 'bbb'),
                    'serverUrls' => array('ccc'),
                    'metarUrls' => array('ddd'),
                    'atisUrls' => array('eee'),
                ),
            ),
            array(
                "nurl3=aaa\nurl3=bbb\nurl1=ccc\nmetar0=ddd\natis0=eee",
                false,
                array(),
            ),
        );
    }

    /**
     *
     * Mock parser object with
     */
    protected function getMockParser($name)
    {
        $class = $this->getMockBuilder('Vatsimphp\Parser\\'.$name.'Parser')
            ->setMethods(null)
            ->getMock();
        return $class;
    }
}
