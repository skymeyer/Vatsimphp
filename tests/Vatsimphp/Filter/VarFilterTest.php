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

class VarFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\VarFilter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Filter\StartOfLineFilter', $class);
    }

    /**
     *
     * Filter test
     * @covers Vatsimphp\Filter\VarFilter::setFilter
     */
    public function testFilter()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\VarFilter')
            ->setConstructorArgs(array(array()))
            ->setMethods(null)
            ->getMock();

        $class->setFilter('myfilter');

        $filterProp = new \ReflectionProperty($class, 'filter');
        $filterProp->setAccessible(true);
        $this->assertEquals("myfilter=", $filterProp->getValue($class));
    }

    /**
     *
     * Iterator test
     * @dataProvider providerTestIterator
     * @covers Vatsimphp\Filter\VarFilter::current
     */
    public function testIterator($filter, $data, $expectedResult)
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\VarFilter')
            ->setConstructorArgs(array($data))
            ->setMethods(null)
            ->getMock();

        $class->setFilter($filter);
        $this->assertSame($expectedResult, $class->toArray());
    }

    public function providerTestIterator()
    {
        $data = array(
            'url0=http://www.pcflyer.net/DataFeed/vatsim-data.txt',
            'url1=http://fsproshop.com/servinfo/vatsim-servers.txt',
            'url0=http://www.klain.net/sidata/vatsim-data.txt     ',
            'metar0=http://metar.vatsim.net/metar.php',
        );
        return array(
            array(
                'url0', $data, array(
                    0 => 'http://www.pcflyer.net/DataFeed/vatsim-data.txt',
                    2 => 'http://www.klain.net/sidata/vatsim-data.txt',
                )
            ),
            array(
                'url1', $data, array(
                    1 => 'http://fsproshop.com/servinfo/vatsim-servers.txt',
                )
            ),
            array(
                'metar0', $data, array(
                    3 => 'http://metar.vatsim.net/metar.php',
                )
            ),
        );
    }
}
