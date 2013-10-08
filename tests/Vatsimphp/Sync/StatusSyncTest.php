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

class StatusSyncTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\StatusSync')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Sync\AbstractSync', $class);
    }

    /**
     *
     * Test defaults
     * @covers Vatsimphp\Sync\StatusSync::setDefaults
     */
    public function testSetDefaults()
    {
        $class = $this->getMockStatusSync();
        $class->setDefaults();
        $this->assertSame(
            array('http://status.vatsim.net/status.txt'),
            $class->getUrls()
        );
    }

    /**
     *
     * Return mock for StatusSync
     */
    protected function getMockStatusSync()
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\StatusSync')
            ->setMethods(null)
            ->getMock();
        return $class;
    }
}
