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

class IteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\Iterator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Filter\AbstractFilter', $class);
    }

    /**
     *
     * Constructor test
     * @covers Vatsimphp\Filter\Iterator::__construct
     */
    public function testCtor()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\Iterator')
            ->setConstructorArgs(array(array()))
            ->setMethods(null)
            ->getMock();

        // protected skipComments
        $skipProp = new \ReflectionProperty($class, 'skipComments');
        $skipProp->setAccessible(true);
        $this->assertFalse($skipProp->getValue($class));
    }

    /**
     *
     * applyFilter test
     * @covers Vatsimphp\Filter\Iterator::applyFilter
     */
    public function testApplyFilter()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\Iterator')
            ->setConstructorArgs(array(array()))
            ->setMethods(null)
            ->getMock();
        $this->assertTrue($class->applyFilter());
    }
}
