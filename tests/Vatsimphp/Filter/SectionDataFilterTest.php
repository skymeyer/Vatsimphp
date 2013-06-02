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

class SectionDataFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\SectionDataFilter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Filter\SectionFilter', $class);
    }

    /**
     *
     * Set header test
     * @covers Vatsimphp\Filter\SectionDataFilter::setHeader
     */
    public function testSetHeader()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\SectionDataFilter')
            ->setConstructorArgs(array(array()))
            ->setMethods(null)
            ->getMock();

        // protected header
        $headerProp = new \ReflectionProperty($class, 'header');
        $headerProp->setAccessible(true);
        $this->assertSame(array(), $headerProp->getValue($class));

        // update header
        $newHeader = array('head1' => array('data'));
        $class->setHeader($newHeader);
        $this->assertSame($newHeader, $headerProp->getValue($class));
    }

    /**
     *
     * Fix data test
     * @dataProvider providerTestFixData
     * @covers Vatsimphp\Filter\SectionDataFilter::fixData
     */
    public function testFixData($header, $data)
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\SectionDataFilter')
            ->setConstructorArgs(array(array()))
            ->setMethods(null)
            ->getMock();

        $class->setHeader($header);

        // protected method fixData
        $fixData = new \ReflectionMethod($class, 'fixData');
        $fixData->setAccessible(true);
        $result = $fixData->invoke($class, $data);

        $this->assertCount(count($header), $result);
    }

    public function providerTestFixData()
    {
        return array(
            array(
                array('callsign', 'cid', 'realname'),
                array('SWA3437', '123456', 'Jelle Vink'),
            ),
            array(
                array('callsign', 'cid', 'realname'),
                array('SWA3437', '123456'),
            ),
        );
    }

    /**
     *
     * Current test
     * @dataProvider providerTestCurrent
     * @covers Vatsimphp\Filter\SectionDataFilter::current
     */
    public function testCurrent($section, $header, $data, $expectedResult)
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\SectionDataFilter')
            ->setConstructorArgs(array($data))
            ->setMethods(null)
            ->getMock();

        $class->setFilter($section);
        $class->setHeader($header);
        $class->rewind();
        $this->assertSame($expectedResult, $class->current());
    }

    public function providerTestCurrent()
    {
        return array(
            array(
                'clients',
                array(
                    'callsign',
                    'cid',
                    'realname',
                ),
                array(
                    ';comment',
                    '!CLIENTS:',
                    'SWA3437:123456:Jelle Vink KSJC:',
                   ),
                array(
                    'callsign' => 'SWA3437',
                    'cid' => '123456',
                    'realname' => 'Jelle Vink KSJC',
                ),
            ),
            array(
                'clients',
                array(
                    'callsign',
                    'cid',
                    'realname',
                ),
                array(
                    ';comment',
                    '!CLIENTS:',
                    'SWA3437:123456:Jelle Vink KSJC:toomuch:',
                ),
                false,
            ),
            array(
                'clients',
                array(
                    'callsign',
                    'cid',
                    'realname',
                ),
                array(
                    ';comment',
                    '!CLIENTS:',
                    'SWA3437:fixnext',
                   ),
                array(
                    'callsign' => 'SWA3437',
                    'cid' => 'fixnext',
                    'realname' => '',
                ),
            ),
        );
    }
}
