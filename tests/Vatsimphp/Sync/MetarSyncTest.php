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

class MetarSyncTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\MetarSync')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Sync\BaseSync', $class);
    }

    /**
     *
     * Test defaults
     * @covers Vatsimphp\Sync\MetarSync::setDefaults
     */
    public function testSetDefaults()
    {
        $class = $this->getMockMetarSync();
        $class->setDefaults();
        $this->assertSame(600, $class->refreshInterval);
    }

    /**
     *
     * Test set airport and url override
     * @dataProvider providerTestSync
     * @covers Vatsimphp\Sync\MetarSync::setAirport
     * @covers Vatsimphp\Sync\MetarSync::overrideUrl
     */
    public function testSync($airport, $expected)
    {
        $sync = $this->getMockMetarSync();
        $sync->setAirport($airport);

        // icao property
        $icao = new \ReflectionProperty($sync, 'icao');
        $icao->setAccessible(true);
        $this->assertEquals($expected, $icao->getValue($sync));

        // cacheFile property
        $cache = new \ReflectionProperty($sync, 'cacheFile');
        $cache->setAccessible(true);
        $this->assertEquals("metar-{$expected}.txt", $cache->getValue($sync));

        // url override
        $testUrl = "http://foo.bar/test.html";
        $expctedUrl = "{$testUrl}?id={$expected}";
        $override = new \ReflectionMethod($sync, 'overrideUrl');
        $override->setAccessible(true);
        $this->assertEquals($expctedUrl, $override->invoke($sync, $testUrl));
    }

    public function providerTestSync()
    {
        return array(
            array('ksfo', 'KSFO'),
            array('KsFo', 'KSFO'),
            array('KSFO', 'KSFO'),
        );
    }

    /**
     *
     * @dataProvider providerTestAirportException
     * @covers Vatsimphp\Sync\MetarSync::setAirport
     * @expectedException        Vatsimphp\Exception\RuntimeException
     * @expectedExceptionMessage invalid ICAO code
     */
    public function testSetAirportException($icao)
    {
        $sync = $this->getMockMetarSync();
        $sync->setAirport($icao);
    }

    public function providerTestAirportException()
    {
        return array(
            array('123'),
            array('12345'),
        );
    }

    /**
     *
     * Return mock for DataSync
     */
    protected function getMockMetarSync($setMethods = null)
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\MetarSync')
            ->setMethods($setMethods)
            ->getMock();
        return $class;
    }
}
