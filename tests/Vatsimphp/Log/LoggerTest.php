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

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Prefix getter
     * @dataProvider providerTestPrefix
     * @covers Vatsimphp\Log\Logger::getPrefix
     * @covers Vatsimphp\Log\Logger::__construct
     */
    public function testPrefix($prefix)
    {
        $logger = $this->getMockBuilder('Vatsimphp\Log\Logger')
            ->setConstructorArgs(array($prefix))
            ->setMethods(null)
            ->getMock();
        $this->assertEquals($prefix, $logger->getPrefix());
    }

    public function providerTestPrefix()
    {
        return array(
            array(null),
            array(''),
            array('notempty'),
        );
    }

    /**
     *
     * Send message to log
     * @dataProvider providerTestLog
     * @covers Vatsimphp\Log\Logger::log
     * @covers Vatsimphp\Log\Logger::getContext
     */
    public function testLog($prefix, $level, $message, $context, $result)
    {
        $logger = $this->getMockBuilder('Vatsimphp\Log\Logger')
            ->setConstructorArgs(array($prefix))
            ->setMethods(null)
            ->getMock();
        $logger->inUnitTest = true;
        $msg = $logger->log($level, $message, $context);
        $this->assertEquals($result, $msg);
    }

    public function providerTestLog()
    {
        return array(
            array(
                '',
                'debug',
                'log this',
                array(),
                '[DEBUG] log this',
            ),
            array(
                'Prefix',
                'debug',
                'log this',
                array(),
                '[DEBUG] Prefix: log this',
            ),
            array(
                'Prefix',
                'debug',
                'log this',
                array(
                    'k1' => 'v1',
                    'k2' => array('bogus'),
                    'k3' => 'v3',
                ),
                '[DEBUG] Prefix: log this'.PHP_EOL.'k1: v1'.PHP_EOL.'k3: v3',
            ),
        );
    }
}
