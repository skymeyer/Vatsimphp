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

class ResultContainerTest extends \PHPUnit_Framework_TestCase
{
    protected $resultClass = 'Vatsimphp\Filter\Iterator';

    /**
     *
     * Empty/countable interface
     */
    public function testCountable()
    {
        $rc = $this->getMockBuilder('Vatsimphp\Result\ResultContainer')
            ->getMock();
        $this->assertCount(0, $rc);
    }

    /**
     *
     * Access non-existing results
     * @covers Vatsimphp\Result\ResultContainer::get
     * @covers Vatsimphp\Result\ResultContainer::__get
     */
    public function testNonExistingResult()
    {
        $rc = $this->getMockBuilder('Vatsimphp\Result\ResultContainer')
            ->setMethods(null)
            ->getMock();
        $this->assertInstanceOf($this->resultClass, $rc->get('doesnotexist'));
        $this->assertInstanceOf($this->resultClass, $rc->doesnotexist);
    }

    /**
     *
     * Append/overwrite test results
     * @dataProvider providerTestAppendArray
     * @covers Vatsimphp\Result\ResultContainer::get
     * @covers Vatsimphp\Result\ResultContainer::__get
     * @covers Vatsimphp\Result\ResultContainer::getList
     * @covers Vatsimphp\Result\ResultContainer::append
     */
    public function testAppendArray($test1, $data1, $test2, $data2, $count, $list)
    {
        $rc = $this->getMockBuilder('Vatsimphp\Result\ResultContainer')
            ->setMethods(null)
            ->getMock();
        $rc->append($test1, $data1);
        $rc->append($test2, $data2);

        $this->assertCount($count, $rc);
        $this->assertInstanceOf($this->resultClass, $rc->get($test1));
        $this->assertInstanceOf($this->resultClass, $rc->$test1);
        $this->assertInstanceOf($this->resultClass, $rc->get($test2));
        $this->assertInstanceOf($this->resultClass, $rc->$test2);

        $this->assertSame($list, $rc->getList());
    }

    public function providerTestAppendArray()
    {
        $iterator = $this->getMockBuilder('Vatsimphp\Filter\Iterator')
            ->setConstructorArgs(array(array('column2' => 'value2')))
            ->getMock();

        return array(
            array(
                'test1',
                array('column1' => 'value1'),
                'test2',
                $iterator,
                2,
                array('test1', 'test2'),
            ),
            array(
                'test3',
                array('column3' => 'value3'),
                'test3',
                array('column3' => 'value3'),
                1,
                array('test3'),
            ),

        );
    }
}
