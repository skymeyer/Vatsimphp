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

use PHPUnit\Framework\TestCase;
use Vatsimphp\Log\LoggerFactory;

class LoggerFactoryTest extends TestCase
{
    protected function tearDown()
    {
        LoggerFactory::deregister();
    }

    /**
     * Base factory test using built-in logger.
     *
     * @dataProvider providerTestBaseFactory
     * @covers Vatsimphp\Log\LoggerFactory::get
     * @covers Vatsimphp\Log\LoggerFactory::channelExists
     */
    public function testBaseFactory($channel, $expectedPrefix)
    {
        $logger = LoggerFactory::get($channel);
        $this->assertInstanceOf('Vatsimphp\Log\Logger', $logger);
        $this->assertTrue(LoggerFactory::channelExists($expectedPrefix));
    }

    public function providerTestBaseFactory()
    {
        return [
            ['', ''],
            ['channel1', 'channel1'],
            [new \stdClass(), 'stdClass'],
        ];
    }

    /**
     * Check for non-existing channel.
     *
     * @covers Vatsimphp\Log\LoggerFactory::channelExists
     */
    public function testChannelNotExists()
    {
        $this->assertFalse(LoggerFactory::channelExists('bogus'));
    }

    /**
     * Register custom log objects.
     *
     * @dataProvider providerTestRegisterChannel
     * @covers Vatsimphp\Log\LoggerFactory::register
     * @covers Vatsimphp\Log\LoggerFactory::deregister
     */
    public function testRegisterDeregister($channel, $logger)
    {
        LoggerFactory::register($channel, $logger);
        $this->assertTrue(LoggerFactory::channelExists($channel));
        LoggerFactory::deregister($channel);
        $this->assertFalse(LoggerFactory::channelExists($channel));
    }

    public function providerTestRegisterChannel()
    {
        return [
            ['channel1', $this->getMockLog('Vatsimphp\Log\Logger')],
            ['channel2', $this->getMockLog('Monolog\Logger')],
        ];
    }

    /**
     * Test override of default logger.
     *
     * @dataProvider providerTestRegisterCustomDefault
     * @covers Vatsimphp\Log\LoggerFactory::register
     * @covers Vatsimphp\Log\LoggerFactory::get
     */
    public function testRegisterCustomDefault($class)
    {
        LoggerFactory::deregister();

        $this->assertFalse(LoggerFactory::channelExists(LoggerFactory::DEFAULT_LOGGER));
        $default = $this->getMockLog($class);

        LoggerFactory::register(LoggerFactory::DEFAULT_LOGGER, $default);
        $this->assertTrue(LoggerFactory::channelExists(LoggerFactory::DEFAULT_LOGGER));

        $new = LoggerFactory::get('new');
        $this->assertInstanceOf($class, $new);
    }

    public function providerTestRegisterCustomDefault()
    {
        return [
            ['Monolog\Logger'],
            ['Vatsimphp\Log\Logger'],
        ];
    }

    /**
     * Deregister all log channels.
     *
     * @covers Vatsimphp\Log\LoggerFactory::deregister
     */
    public function testDeregisterAll()
    {
        $this->assertTrue(LoggerFactory::deregister());
    }

    /**
     * Deregister non-existing log channel.
     *
     * @covers Vatsimphp\Log\LoggerFactory::deregister
     */
    public function testDeregiserNonExisting()
    {
        $this->assertFalse(LoggerFactory::deregister('bogus'));
    }

    /**
     * Return mocked logger object.
     *
     * @param string $class
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function getMockLog($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
