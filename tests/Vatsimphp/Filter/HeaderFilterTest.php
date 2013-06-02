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

class HeaderFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\HeaderFilter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Filter\StartOfLineFilter', $class);
    }

    /**
     *
     * Filter test
     * @covers Vatsimphp\Filter\HeaderFilter::setFilter
     */
    public function testFilter()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\HeaderFilter')
            ->setConstructorArgs(array(array()))
            ->setMethods(null)
            ->getMock();

        $class->setFilter('clients');

        // protected filter
        $filterProp = new \ReflectionProperty($class, 'filter');
        $filterProp->setAccessible(true);
        $this->assertEquals('; !CLIENTS section - ', $filterProp->getValue($class));

        // protected skip comments
        $skipProp = new \ReflectionProperty($class, 'skipComments');
        $skipProp->setAccessible(true);
        $this->assertFalse($skipProp->getValue($class));

    }

    /**
     *
     * Current test
     * @dataProvider providerTestCurrent
     * @covers Vatsimphp\Filter\HeaderFilter::current
     */
    public function testCurrent($data, $expectedResult)
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\HeaderFilter')
            ->setConstructorArgs(array(array($data)))
            ->setMethods(null)
            ->getMock();

        $class->setFilter('clients');
        $class->rewind();
        $this->assertSame($expectedResult, $class->current());
    }

    public function providerTestCurrent()
    {
        return array(
            array(
                '; !CLIENTS section - callsign:cid:realname:',
                array('callsign', 'cid', 'realname'),
            ),
            array(
                '; !CLIENTS section - callsign:cid:realname',
                array('callsign', 'cid', 'realname'),
            ),
            array(
                '; !CLIENTS section - callsign',
                array('callsign'),
            ),
        );
    }
}
