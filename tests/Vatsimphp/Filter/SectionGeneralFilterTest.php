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

class SectionGeneralFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\SectionGeneralFilter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Filter\SectionFilter', $class);
    }

    /**
     *
     * Filter test
     * @covers Vatsimphp\Filter\SectionGeneralFilter::__construct
     */
    public function testCtor()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\SectionGeneralFilter')
            ->setConstructorArgs(array(array()))
            ->setMethods(null)
            ->getMock();

        // protected filter
        $filterProp = new \ReflectionProperty($class, 'filter');
        $filterProp->setAccessible(true);
        $this->assertEquals('!GENERAL:', $filterProp->getValue($class));
    }
}
