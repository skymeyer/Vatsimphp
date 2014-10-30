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

class LoggerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Base factory test using built-in logger
     * @dataProvider providerTestBaseFactory
     * @covers Vatsimphp\Log\LoggerFactory::get
     * @covers Vatsimphp\Log\LoggerFactory::channelExists
     */
    public function testBaseFactory($channel, $expectedPrefix)
    {
        $factory = $this->getMockFactory();
        $logger = $factory::get($channel);
        $this->assertInstanceOf('Vatsimphp\Log\Logger', $logger);
        $this->assertTrue($factory::channelExists($expectedPrefix));
    }

    public function providerTestBaseFactory()
    {
        $class1 = new \stdClass();
        $class2 = $this->getMockBuilder('Vatsimphp\Filter\Iterator')
            ->disableOriginalConstructor()
            ->getMock();

        return array(
            array('', ''),
            array('channel1', 'channel1'),
            array($class1, 'stdClass'),
            array($class2, get_class($class2)),
        );
    }

    /**
     *
     * Check for non-existing channel
     * @covers Vatsimphp\Log\LoggerFactory::channelExists
     */
    public function testChannelNotExists()
    {
        $factory = $this->getMockFactory();
        $this->assertFalse($factory::channelExists('bogus'));
    }

    /**
     *
     * Register custom log objects
     * @dataProvider providerTestRegisterChannel
     * @covers Vatsimphp\Log\LoggerFactory::register
     * @covers Vatsimphp\Log\LoggerFactory::deregister
     */
    public function testRegisterDeregister($channel, $logger)
    {
        $factory = $this->getMockFactory();

        $factory::register($channel, $logger);
        $this->assertTrue($factory::channelExists($channel));
        $factory::deregister($channel);
        $this->assertFalse($factory::channelExists($channel));
    }

    public function providerTestRegisterChannel()
    {
        return array(
            array('channel1', $this->getMockLog('Vatsimphp\Log\Logger')),
            array('channel2', $this->getMockLog('Monolog\Logger')),
        );
    }

    /**
     *
     * Test override of default logger
     * @dataProvider providerTestRegisterCustomDefault
     * @covers Vatsimphp\Log\LoggerFactory::register
     * @covers Vatsimphp\Log\LoggerFactory::get
     */
    public function testRegisterCustomDefault($class)
    {
        $factory = $this->getMockFactory();
        $factory::deregister();

        $this->assertFalse($factory::channelExists($factory::DEFAULT_LOGGER));
        $default = $this->getMockLog($class);

        $factory::register($factory::DEFAULT_LOGGER, $default);
        $this->assertTrue($factory::channelExists($factory::DEFAULT_LOGGER));

        $new = $factory::get('new');
        $this->assertInstanceOf($class, $new);
    }

    public function providerTestRegisterCustomDefault()
    {
        return array(
            array('Monolog\Logger'),
            array('Vatsimphp\Log\Logger'),
        );
    }

    /**
     *
     * Deregister all log channels
     * @covers Vatsimphp\Log\LoggerFactory::deregister
     */
    public function testDeregisterAll()
    {
        $factory = $this->getMockFactory();
        $this->assertTrue($factory::deregister());
    }

    /**
     *
     * Deregister non-existing log channel
     * @covers Vatsimphp\Log\LoggerFactory::deregister
     */
    public function testDeregiserNonExisting()
    {
        $factory = $this->getMockFactory();
        $this->assertFalse($factory::deregister('bogus'));
    }

    /**
     *
     * Return mocked factory
     * @return \Vatsimphp\Log\LoggerFactory
     */
    protected function getMockFactory()
    {
        return $this->getMockBuilder('Vatsimphp\Log\LoggerFactory')
            ->setMethods(null)
            ->getMock();
    }
    /**
     *
     * Return mocked logger object
     * @param string $class
     * @return \Psr\Log\LoggerInterface
     */
    protected function getMockLog($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
