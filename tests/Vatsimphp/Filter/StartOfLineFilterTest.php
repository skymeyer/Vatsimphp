<?php

/*
 * This file is part of the Vatsimphp package
 *
 * Copyright 2018 - Jelle Vink <jelle.vink@gmail.com>
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

class StartOfLineFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test inheritance.
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\VarFilter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Filter\AbstractFilter', $class);
    }

    /**
     * Apply filter test.
     *
     * @dataProvider providerTestApplyFilter
     * @covers Vatsimphp\Filter\StartOfLineFilter::applyFilter
     */
    public function testApplyFilter($startOfLine, $line, $status)
    {
        $class = $this->getMockBuilder('Vatsimphp\Filter\StartOfLineFilter')
            ->setConstructorArgs([[$line]])
            ->setMethods(null)
            ->getMock();
        $class->setFilter($startOfLine);
        $this->assertEquals($status, $class->applyFilter());
    }

    public function providerTestApplyFilter()
    {
        return [
            ['', 'full line', true],
            ['start', 'start=result', true],
            ['notexist', 'start=result', false],
            [';', ';COMMENTS', true],
            [';', 'just text', false],
        ];
    }
}
