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
     * Return mock for DataSync with silent logger
     */
    protected function getMockDataSync()
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\DataSync')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        return $this->attachMockedLogger($class);
    }

    /**
     *
     * Attach mocked silent logger
     */
    protected function attachMockedLogger($class)
    {
        $silentLogger = $this->getMockBuilder('Vatsimphp\Log\Logger')
            ->getMock();
        $logger = new \ReflectionProperty($class, 'log');
        $logger->setAccessible(true);
        $logger->setValue($class, $silentLogger);
        return $class;
    }
}
