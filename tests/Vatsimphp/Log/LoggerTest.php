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

use Vatsimphp\Log\Logger;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Logger::resetHandler();
        $dir = 'build/tests';
        @mkdir($dir, 0777, true);
    }

    protected function tearDown()
    {
        Logger::resetHandler();
        $dir = 'build/tests';
        @unlink($dir.'/file.log');
    }

    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Log\Logger')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Psr\Log\LoggerInterface', $class);
    }

    /**
     *
     * @covers Vatsimphp\Log\Logger::__construct
     * @covers Vatsimphp\Log\Logger::getHandler
     * @covers Vatsimphp\Log\Logger::getCustomFormatter
     */
    public function testLogger()
    {
        $logger = $this->getMockBuilder('Vatsimphp\Log\Logger')
            ->setConstructorArgs(array('foo', 'build/tests/file.log', Logger::CRITICAL))
            ->setMethods(null)
            ->getMock();
        $this->assertEquals('foo', $logger->getName());
        $this->assertTrue($logger->critical('logthis'));
    }
}
