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

class DataSyncTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\DataSync')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Sync\BaseSync', $class);
    }

    /**
     *
     * Test defaults
     * @covers Vatsimphp\Sync\DataSync::setDefaults
     */
    public function testSetDefaults()
    {
        $class = $this->getMockDataSync();
        $class->setDefaults();
        $this->assertSame(180, $class->refreshInterval);
    }

    /**
     *
     * Test data validation based on expire setting
     * @dataProvider providerTestIsDataValid
     * @covers Vatsimphp\Sync\DataSync::isDataValid
     */
    public function testIsDataValid($expire)
    {
        $class = $this->getMockDataSync();
        $class->dataExpire = $expire;

        // attach mocked parser objects
        $parser = $this->getMockBuilder('Vatsimphp\Parser\DataParser')
            ->disableOriginalConstructor()
            ->setMethods(array('setData', 'parseData'))
            ->getMock();

        $parserProp = new \ReflectionProperty($class, 'parser');
        $parserProp->setAccessible(true);
        $parserProp->setValue($class, $parser);

        $isDataValid = new \ReflectionMethod($class, 'isDataValid');
        $isDataValid->setAccessible(true);
        $isDataValid->invoke($class, 'bogus_data');

        // expire flag should be passed to parser
        $this->assertEquals($expire, $parserProp->getValue($class)->dataExpire);

    }

    public function providerTestIsDataValid()
    {
        return array(
            array(0),
            array(1),
            array(999),
        );
    }

    /**
     *
     * Return mock for DataSync
     */
    protected function getMockDataSync()
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\DataSync')
            ->setMethods(null)
            ->getMock();
        return $class;
    }
}
