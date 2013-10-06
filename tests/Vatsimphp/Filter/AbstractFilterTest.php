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

class AbstractFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Setter/getter filter
     * @dataProvider providerTestSetGetFilter
     * @covers Vatsimphp\Filter\AbstractFilter::setFilter
     */
    public function testSetGetFilter($filter, $expectedFilter)
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\AbstractFilter')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $class->setFilter($filter);

        // protected property filter
        $property = new \ReflectionProperty($class, 'filter');
        $property->setAccessible(true);
        $this->assertSame($expectedFilter, $property->getValue($class));
    }

    public function providerTestSetGetFilter()
    {
        return array(
            array('filter1', 'filter1'),
            array('filter 2', 'filter 2'),
            array(array(), ''),
        );
    }

    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\AbstractFilter')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assertInstanceOf('\Countable', $class);
        $this->assertInstanceOf('\FilterIterator', $class);
        $this->assertInstanceOf('Vatsimphp\Filter\FilterInterface', $class);
    }

    /**
     *
     * Iterator test
     * @dataProvider providerTestIterator
     * @covers Vatsimphp\Filter\AbstractFilter::__construct
     * @covers Vatsimphp\Filter\AbstractFilter::count
     * @covers Vatsimphp\Filter\AbstractFilter::accept
     * @covers Vatsimphp\Filter\AbstractFilter::current
     */
    public function testIterator($data, $negate, $skip, $count)
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\AbstractFilter')
            ->setConstructorArgs(array($data))
            ->setMethods(array('applyFilter'))
            ->getMockForAbstractClass();

        // stub applyFilter
        $class->expects($this->any())
            ->method('applyFilter')
            ->will($this->returnValue(true));

        // protected negate flag
        $negateProp = new \ReflectionProperty($class, 'negate');
        $negateProp->setAccessible(true);
        $negateProp->setValue($class, $negate);

        // protected skipComments flag
        $skipProp = new \ReflectionProperty($class, 'skipComments');
        $skipProp->setAccessible(true);
        $skipProp->setValue($class, $skip);

        $this->assertCount($count, $class);
    }

    public function providerTestIterator()
    {
        return array(
            // no negation
            array(array(),              false, false, 0),
            array(array(1, 't'),        false, false, 2),
            array(array(1, 't', ';3'),  false, false, 3),
            // negation
            array(array(),              true, false, 0),
            array(array(1, 't'),        true, false, 0),
            array(array(1, 't', ';3'),  true, false, 0),
            // skip comments
            array(array(),              false, true, 0),
            array(array(1, 't'),        false, true, 2),
            array(array(1, 't', ';3'),  false, true, 2),
        );
    }

    /**
     * String to array helper
     * @dataProvider providerTestConvertToArray
     * @covers Vatsimphp\Filter\AbstractFilter::convertToArray
     */
    public function testConvertToArray($data, $sep, $expectedResult)
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\AbstractFilter')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $convert = new \ReflectionMethod($class, 'convertToArray');
        $convert->setAccessible(true);
        $converted = $convert->invoke($class, $data, $sep);
        $this->assertSame($expectedResult, $converted);
    }

    public function providerTestConvertToArray()
    {
        return array(
            array(
                'SWA3437:123456:Jelle Vink KSJC:PILOT::34.45314:-108.27796:',
                ':',
                array(
                    'SWA3437',
                    '123456',
                    'Jelle Vink KSJC',
                    'PILOT',
                    '',
                    '34.45314',
                    '-108.27796'
                ),
            ),
            array(
                'SWA3437:123456:Jelle Vink KSJC:PILOT::34.45314:-108.27796',
                ':',
                array(
                    'SWA3437',
                    '123456',
                    'Jelle Vink KSJC',
                    'PILOT',
                    '',
                    '34.45314',
                    '-108.27796'
                ),
            ),
            array(
                ':123456:Jelle Vink KSJC:PILOT::34.45314:-108.27796:',
                ':',
                array(
                    '',
                    '123456',
                    'Jelle Vink KSJC',
                    'PILOT',
                    '',
                    '34.45314',
                    '-108.27796'
                ),
            ),
        );
    }

    /**
     * Iterator to array
     * @dataProvider providerTestIteratorToArray
     * @covers Vatsimphp\Filter\AbstractFilter::toArray
     */
    public function testIteratorToArray($data, $retainKeys, $expectedResult)
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\AbstractFilter')
            ->setConstructorArgs(array($data))
            ->setMethods(array('applyFilter'))
            ->getMockForAbstractClass();

        // stub applyFilter
        $class->expects($this->any())
            ->method('applyFilter')
            ->will($this->returnValue(true));

        $this->assertSame($expectedResult, $class->toArray($retainKeys));
    }

    public function providerTestIteratorToArray()
    {
        $data = array(
             0  => 'zero',
             1  => array('one'),
            '2' => null,
             3  => false,
            '4' => 0,
            'f' => 'five',
        );

        return array(
             array($data, true,
                array(
                     0  => 'zero',
                     1  => array('one'),
                    '2' => null,
                     3  => false,
                    '4' => 0,
                    'f' => 'five',
                )
            ),
             array($data, false,
                array(
                     0  => 'zero',
                     1  => array('one'),
                     2  => null,
                     3  => false,
                     4  => 0,
                     5 => 'five',
                )
            ),
        );
    }

    /**
     * Test comments
     * @dataProvider providerTestComments
     * @covers Vatsimphp\Filter\AbstractFilter::isComment
     */
    public function testComments($data, $expectedResult)
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\AbstractFilter')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        // protected isComment method
        $convert = new \ReflectionMethod($class, 'isComment');
        $convert->setAccessible(true);
        $converted = $convert->invoke($class, $data);
        $this->assertSame($expectedResult, $converted);
    }

    public function providerTestComments()
    {
        return array(
            array(';comment', true),
            array('nocomment', false),
        );
    }

    /**
     *
     * Test default value properties
     * @dataProvider providerTestProperties
     */
    public function testProperties($property, $value)
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\AbstractFilter')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        // protected property
        $property = new \ReflectionProperty($class, $property);
        $property->setAccessible(true);
        $this->assertSame($value, $property->getValue($class));
    }

    public function providerTestProperties()
    {
        return array(
            array('filter', ''),
            array('skipComments', true),
            array('negate', false),
        );
    }
}
