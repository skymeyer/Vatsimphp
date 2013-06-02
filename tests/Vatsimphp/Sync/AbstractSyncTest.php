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

class AbstractSyncTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $dir = 'build/tests';
        @mkdir($dir);
        touch($dir.'/writeable.test');
        touch($dir.'/unwriteable.test');
        chmod($dir.'/unwriteable.test', 0400);
    }

    protected function tearDown()
    {
        $dir = 'build/tests';
        @unlink($dir.'/writeable.test');
        @unlink($dir.'/unwriteable.test');
        @unlink($dir.'/newwriteable.test');
    }

    /**
     *
     * Test default settings
     * @covers Vatsimphp\Sync\AbstractSync::__construct
     */
    public function testDefaults()
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\AbstractSync')
            ->getMockForAbstractClass();

        // protected log
        $propLog = new \ReflectionProperty($class, 'log');
        $propLog->setAccessible(true);
        $this->assertInstanceOf('Psr\Log\LoggerInterface', $propLog->getValue($class));

    }

    /**
     *
     * Test config validation (valid)
     * @dataProvider providerTestValidateConfig
     * @covers Vatsimphp\Sync\AbstractSync::validateConfig
     * @covers Vatsimphp\Sync\AbstractSync::validateUrls
     * @covers Vatsimphp\Sync\AbstractSync::validateRefreshInterval
     * @covers Vatsimphp\Sync\AbstractSync::validateCacheFile
     * @covers Vatsimphp\Sync\AbstractSync::validateFilePath
     * @covers Vatsimphp\Sync\AbstractSync::validateParser
     */
    public function testValidateConfig($data)
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\AbstractSync')
            ->getMockForAbstractClass();

        // prepare test values
        foreach ($data as $property => $value) {
            $propReflect = new \ReflectionProperty($class, $property);
            $propReflect->setAccessible(true);
            $propReflect->setValue($class, $value);
        }

        $sut = new \ReflectionMethod($class, 'validateConfig');
        $sut->setAccessible(true);
        $this->assertTrue($sut->invoke($class, array()));
    }

    public function providerTestValidateConfig()
    {
        $parser = $this->getMockBuilder('Vatsimphp\Parser\AbstractParser')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return array(
            array(
                array(
                    'urls' => array('http://link'),
                    'refreshInterval' => 30,
                    'cacheFile' => 'notempty',
                    'filePath' => 'build/tests/writeable.test',
                    'parser' => $parser,
                ),
                array(
                    'urls' => array('http://link'),
                    'refreshInterval' => 30,
                    'cacheFile' => 'notempty',
                    'filePath' => 'build/tests/newwriteable.test',
                    'parser' => $parser,
                ),
            ),
        );
    }

    /**
     *
     * Test config validation - UnexpectedValueException
     * @dataProvider providerTestValidateConfigUnexpectedValue
     * @covers Vatsimphp\Sync\AbstractSync::validateUrls
     * @covers Vatsimphp\Sync\AbstractSync::validateRefreshInterval
     * @covers Vatsimphp\Sync\AbstractSync::validateCacheFile
     * @expectedException Vatsimphp\Exception\UnexpectedValueException
     */
    public function testValidateConfigUnexpectedValue($property, $value)
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\AbstractSync')
            ->getMockForAbstractClass();

        // access protected property
        $propReflect = new \ReflectionProperty($class, $property);
        $propReflect->setAccessible(true);
        $propReflect->setValue($class, $value);

        // access protected method
        $validationMethod = "validate" . ucfirst($property);
        $sut = new \ReflectionMethod($class, $validationMethod);
        $sut->setAccessible(true);
        $sut->invoke($class, array());
    }

    public function providerTestValidateConfigUnexpectedValue()
    {
        return array(
            array('urls', null),
            array('refreshInterval', 'bogus'),
            array('cacheFile', ''),
        );
    }

    /**
     *
     * Test config validation - RuntimeException
     * @dataProvider providerTestValidateConfigRuntime
     * @covers Vatsimphp\Sync\AbstractSync::validateFilePath
     * @covers Vatsimphp\Sync\AbstractSync::validateParser
     * @expectedException Vatsimphp\Exception\RuntimeException
     */
    public function testValidateConfigRuntime($property, $value)
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\AbstractSync')
            ->getMockForAbstractClass();

        // access protected property
        $propReflect = new \ReflectionProperty($class, $property);
        $propReflect->setAccessible(true);
        $propReflect->setValue($class, $value);

        // access protected method
        $validationMethod = "validate" . ucfirst($property);
        $sut = new \ReflectionMethod($class, $validationMethod);
        $sut->setAccessible(true);
        $sut->invoke($class, array());
    }

    public function providerTestValidateConfigRuntime()
    {
        return array(
            array('filePath', '/bogus/noexist.test'),
            array('filePath', 'build/tests/unwriteable.test'),
            array('parser', null),
        );
    }
}
