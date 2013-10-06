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

class SectionFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\SectionFilter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Filter\AbstractFilter', $class);
    }

    /**
     *
     * Filter test
     * @covers Vatsimphp\Filter\SectionFilter::setFilter
     */
    public function testFilter()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\SectionFilter')
            ->setConstructorArgs(array(array()))
            ->setMethods(null)
            ->getMock();

        $class->setFilter('clients');

        // protected filter
        $filterProp = new \ReflectionProperty($class, 'filter');
        $filterProp->setAccessible(true);
        $this->assertEquals('!CLIENTS:', $filterProp->getValue($class));

        // protected inSection
        $skipProp = new \ReflectionProperty($class, 'inSection');
        $skipProp->setAccessible(true);
        $this->assertFalse($skipProp->getValue($class));

    }

    /**
     *
     * Test filter section
     * @dataProvider providerTestApplyFilter
     * @covers Vatsimphp\Filter\SectionFilter::applyFilter
     */
    public function testApplyFilter($section, $data, $expectedResult)
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\SectionFilter')
            ->setConstructorArgs(array($data))
            ->setMethods(null)
            ->getMock();

        $class->setFilter($section);
        $this->assertSame($expectedResult, array_values($class->toArray(false, false)));
    }

    public function providerTestApplyFilter()
    {
        $baseData = array(
            ';comment line',
            ';',
            '!CLIENTS:',
            'SWA3437:1234567:Jelle Vink KSJC:',
            'BWA3892:8901234:Jelle Vink EBBR:',
            ';',
            '!VOICE SERVERS:',
            'rw.liveatc.net:North America, USA, California:Liveatc:1:R:',
            '',
            ';',
        );

        return array(
            array(
                'clients', $baseData,
                array(
                    'SWA3437:1234567:Jelle Vink KSJC:',
                    'BWA3892:8901234:Jelle Vink EBBR:',
                ),
            ),
            array(
                'voice servers', $baseData,
                array(
                    'rw.liveatc.net:North America, USA, California:Liveatc:1:R:',
                ),
            ),
        );
    }
}
