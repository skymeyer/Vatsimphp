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

use Vatsimphp\Filter\Iterator;
use Vatsimphp\Log\Logger;
use Vatsimphp\Log\LoggerFactory;
use Vatsimphp\Result\ResultContainer;

class VatsimDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test config setter/getter.
     *
     * @covers Vatsimphp\VatsimData::getConfig
     * @covers Vatsimphp\VatsimData::setConfig
     */
    public function testConfig()
    {
        $data = $this->getDataMock();

        // available config keys
        $this->assertArrayHasKey('cacheDir', $data->getConfig());
        $this->assertArrayHasKey('cacheOnly', $data->getConfig());
        $this->assertArrayHasKey('statusUrl', $data->getConfig());
        $this->assertArrayHasKey('statusRefresh', $data->getConfig());
        $this->assertArrayHasKey('dataRefresh', $data->getConfig());
        $this->assertArrayHasKey('dataExpire', $data->getConfig());
        $this->assertArrayHasKey('forceDataRefresh', $data->getConfig());
        $this->assertArrayHasKey('metarRefresh', $data->getConfig());
        $this->assertArrayHasKey('forceMetarRefresh', $data->getConfig());

        // not accepting unknown keys
        $data->setConfig('foo', 'bar');
        $this->assertArrayNotHasKey('foo', $data->getConfig());
        $this->assertNull($data->getConfig('foo'));

        // update keys
        $data->setConfig('cacheDir', '/tmp/bogus/dir');
        $this->assertArrayHasKey('cacheDir', $data->getConfig());
        $this->assertSame('/tmp/bogus/dir', $data->getConfig('cacheDir'));
    }

    /**
     * @covers Vatsimphp\VatsimData::search
     */
    public function testSearch()
    {
        $container = $this->getContainerMock(['search']);
        $container->expects($this->once())
            ->method('search')
            ->with($this->equalTo('general'), $this->equalTo('query'));
        $data = $this->attachContainer($this->getDataMock(), $container);
        $data->search('general', 'query');
    }

    /**
     * Tests for "easy" api calls using VatsimData::search.
     *
     * @dataProvider providerTestEasySearch
     * @covers Vatsimphp\VatsimData::searchCallsign
     * @covers Vatsimphp\VatsimData::searchVatsimId
     * @covers Vatsimphp\VatsimData::getPilots
     * @covers Vatsimphp\VatsimData::getControllers
     */
    public function testEasySearch($method, $object, $field, $term)
    {
        $data = $this->getDataMock(['search']);
        $container = $this->getContainerMock();
        $data = $this->attachContainer($data, $container);
        $data->expects($this->once())
            ->method('search')
            ->with($this->equalTo($object), $this->equalTo([$field => $term]));
        $data->$method($term);
    }

    public function providerTestEasySearch()
    {
        return [
            ['searchCallsign', 'clients', 'callsign', 'SWA3437'],
            ['searchVatsimId', 'clients', 'cid', '123456'],
            ['getPilots', 'clients', 'clienttype', 'PILOT'],
            ['getControllers', 'clients', 'clienttype', 'ATC'],
        ];
    }

    /**
     * Tests for "easy" api calls using VatsimData::getIterator.
     *
     * @dataProvider providerTestEasyIterator
     * @covers Vatsimphp\VatsimData::getGeneralInfo
     * @covers Vatsimphp\VatsimData::getClients
     * @covers Vatsimphp\VatsimData::getServers
     * @covers Vatsimphp\VatsimData::getVoiceServers
     * @covers Vatsimphp\VatsimData::getPrefile
     */
    public function testEasyIterator($method, $object)
    {
        $data = $this->getDataMock(['getIterator']);
        $container = $this->getContainerMock();
        $data = $this->attachContainer($data, $container);
        $data->expects($this->once())
            ->method('getIterator')
            ->with($this->equalTo($object));
        $data->$method();
    }

    public function providerTestEasyIterator()
    {
        return [
            ['getGeneralInfo', 'general'],
            ['getClients', 'clients'],
            ['getServers', 'servers'],
            ['getVoiceServers', 'voice_servers'],
            ['getPrefile', 'prefile'],
        ];
    }

    /**
     * @dataProvider providerTestGetMetar
     * @covers Vatsimphp\VatsimData::getMetar
     */
    public function testGetMetar($loadData, $metar, $expected)
    {
        $data = $this->getDataMock(['loadMetar']);
        $data->expects($this->once())
            ->method('loadMetar')
            ->will($this->returnValue($loadData));

        $container = $this->getContainerMock();
        $container->append('metar', [$metar]);
        $data = $this->attachContainer($data, $container);

        $this->assertEquals($expected, $data->getMetar('KSFO'));
    }

    public function providerTestGetMetar()
    {
        return [
            [true, 'metar content', 'metar content'],
            [false, 'metar content', ''],
        ];
    }

    /**
     * @covers Vatsimphp\VatsimData::getObjectTypes
     */
    public function testGetObjectTypes()
    {
        $container = $this->getContainerMock(['getList']);
        $container->expects($this->once())
            ->method('getList');
        $data = $this->attachContainer($this->getDataMock(), $container);
        $data->getObjectTypes();
    }

    /**
     * @covers Vatsimphp\VatsimData::getIterator
     * @covers Vatsimphp\VatsimData::__get
     */
    public function testGetIterator()
    {
        $container = $this->getContainerMock(['get']);
        $container->expects($this->any())
            ->method('get')
            ->with($this->equalTo('general'));
        $data = $this->attachContainer($this->getDataMock(), $container);

        $it1 = $data->getIterator('general');
        $it2 = $data->general;

        $this->assertSame($it1, $it2);
    }

    /**
     * @covers Vatsimphp\VatsimData::getArray
     */
    public function testGetArray()
    {
        // iterator mock for getIterator to be able to call toArray
        $iterator = $this->getMockBuilder('Vatsimphp\Filter\AbstractFilter')
            ->setConstructorArgs([[]])
            ->setMethods([])
            ->getMockForAbstractClass();

        $data = $this->getDataMock(['getIterator']);
        $container = $this->getContainerMock();
        $data = $this->attachContainer($data, $container);
        $data->expects($this->once())
            ->method('getIterator')
            ->with($this->equalTo('general'))
            ->will($this->returnValue($iterator));
        $data->getArray('general');
    }

    /**
     * @covers Vatsimphp\VatsimData::loadData
     */
    public function testLoadData()
    {
        $statusSync = $this->getSyncMock('status');

        $dataSync = $this->getSyncMock('data');
        $dataSync->expects($this->once())
            ->method('setDefaults');
        $dataSync->expects($this->once())
            ->method('registerUrlFromStatus');
        $dataSync->expects($this->once())
            ->method('loadData');

        $data = $this->getDataMock(['prepareSync', 'getDataSync']);
        $data->expects($this->once())
            ->method('prepareSync')
            ->will($this->returnValue($statusSync));
        $data->expects($this->once())
            ->method('getDataSync')
            ->will($this->returnValue($dataSync));

        $this->assertTrue($data->loadData());
    }

    /**
     * Exception stack test.
     *
     * @covers Vatsimphp\VatsimData::loadData
     * @covers Vatsimphp\VatsimData::getExceptionStack
     */
    public function testLoadDataException()
    {
        $statusSync = $this->getSyncMock('status');

        $dataSync = $this->getSyncMock('data');
        $dataSync->expects($this->once())
            ->method('loadData')
            ->will($this->throwException(new \Exception()));

        $data = $this->getDataMock(['prepareSync', 'getDataSync']);
        $data->expects($this->once())
            ->method('prepareSync')
            ->will($this->returnValue($statusSync));
        $data->expects($this->once())
            ->method('getDataSync')
            ->will($this->returnValue($dataSync));

        $this->assertFalse($data->loadData());
        $stack = $data->getExceptionStack();
        $this->assertCount(1, $stack);
        $this->assertArrayHasKey(0, $stack);
        $this->assertInstanceOf('\Exception', $stack[0]);
    }

    /**
     * @covers Vatsimphp\VatsimData::loadMetar
     */
    public function testLoadMetar()
    {
        $metarSync = $this->getSyncMock('metar');
        $metarSync->expects($this->once())
            ->method('setAirport')
            ->with($this->equalTo('KSFO'));
        $metarSync->expects($this->once())
            ->method('loadData');

        $data = $this->getDataMock(['prepareMetarSync']);
        $data->expects($this->once())
            ->method('prepareMetarSync')
            ->will($this->returnValue($metarSync));

        $this->assertTrue($data->loadMetar('KSFO'));
    }

    /**
     * Exception stack test.
     *
     * @covers Vatsimphp\VatsimData::loadMetar
     * @covers Vatsimphp\VatsimData::getExceptionStack
     */
    public function testLoadMetarException()
    {
        $metarSync = $this->getSyncMock('metar');
        $metarSync->expects($this->once())
            ->method('loadData')
            ->will($this->throwException(new \Exception()));

        $data = $this->getDataMock(['prepareMetarSync']);
        $data->expects($this->once())
            ->method('prepareMetarSync')
            ->will($this->returnValue($metarSync));

        $this->assertFalse($data->loadMetar('KSFO'));
        $stack = $data->getExceptionStack();
        $this->assertCount(1, $stack);
        $this->assertArrayHasKey(0, $stack);
        $this->assertInstanceOf('\Exception', $stack[0]);
    }

    /**
     * @covers Vatsimphp\VatsimData::prepareSync
     */
    public function testPrepareSync()
    {
        $statusSync = $this->getSyncMock('status');
        $statusSync->expects($this->once())
            ->method('setDefaults');
        $statusSync->expects($this->once())
            ->method('registerUrl')
            ->with($this->equalTo('custom_url'), $this->equalTo(true));

        LoggerFactory::$level = Logger::DEBUG;
        LoggerFactory::$file = null;

        $data = $this->getDataMock(['getStatusSync']);
        $data->setConfig('statusUrl', 'custom_url');
        $data->setConfig('logLevel', Logger::CRITICAL);
        $data->setConfig('logFile', 'test.log');
        $data->expects($this->once())
            ->method('getStatusSync')
            ->will($this->returnValue($statusSync));

        $prepare = new \ReflectionMethod($data, 'prepareSync');
        $prepare->setAccessible(true);
        $this->assertInstanceOf('Vatsimphp\Sync\StatusSync', $prepare->invoke($data));

        $this->assertEquals(Logger::CRITICAL, LoggerFactory::$level);
        $this->assertEquals('test.log', LoggerFactory::$file);
    }

    /**
     * @covers Vatsimphp\VatsimData::prepareMetarSync
     */
    public function testPrepareMetarSync()
    {
        $statusSync = $this->getSyncMock('status');
        $metarSync = $this->getSyncMock('metar');
        $metarSync->expects($this->once())
            ->method('setDefaults');
        $metarSync->expects($this->once())
            ->method('registerUrlFromStatus')
            ->with($this->isInstanceOf('Vatsimphp\Sync\StatusSync'), $this->equalTo('metarUrls'));

        $data = $this->getDataMock(['prepareSync', 'getMetarSync']);
        $data->expects($this->once())
            ->method('prepareSync')
            ->will($this->returnValue($statusSync));
        $data->expects($this->once())
            ->method('getMetarSync')
            ->will($this->returnValue($metarSync));

        $prepare = new \ReflectionMethod($data, 'prepareMetarSync');
        $prepare->setAccessible(true);
        $this->assertInstanceOf('Vatsimphp\Sync\MetarSync', $prepare->invoke($data));
    }

    /**
     * Test proper return of cached objects to avoid multiple invocations.
     *
     * @dataProvider providerTestCachedObjects
     * @covers Vatsimphp\VatsimData::prepareSync
     * @covers Vatsimphp\VatsimData::prepareMetarSync
     */
    public function testCachedObjects($name, $cacheProp, $notCallFunction, $class, $testMethod)
    {
        $object = $this->getSyncMock($name);

        $data = $this->getDataMock([$notCallFunction]);
        $data->expects($this->never())
            ->method($notCallFunction);

        $cache = new \ReflectionProperty($data, $cacheProp);
        $cache->setAccessible(true);
        $cache->setValue($data, $object);

        $test = new \ReflectionMethod($data, $testMethod);
        $test->setAccessible(true);
        $this->assertInstanceOf($class, $test->invoke($data));
    }

    public function providerTestCachedObjects()
    {
        return [
            ['status', 'statusSync', 'getStatusSync', 'Vatsimphp\Sync\StatusSync', 'prepareSync'],
            ['metar', 'metarSync', 'getMetarSync', 'Vatsimphp\Sync\MetarSync', 'prepareMetarSync'],
        ];
    }

    /**
     * @dataProvider providerTestSyncGetters
     * @covers Vatsimphp\VatsimData::getStatusSync
     * @covers Vatsimphp\VatsimData::getDataSync
     * @covers Vatsimphp\VatsimData::getMetarSync
     */
    public function testSyncGetters($name)
    {
        $data = $this->getDataMock();
        $getMethod = "get{$name}Sync";
        $classname = "Vatsimphp\\Sync\\{$name}Sync";
        $reflection = new \ReflectionMethod($data, $getMethod);
        $reflection->setAccessible(true);
        $this->assertInstanceOf($classname, $reflection->invoke($data));
    }

    public function providerTestSyncGetters()
    {
        return [
            ['Status'],
            ['Data'],
            ['Metar'],
        ];
    }

    /**
     * @covers Vatsimphp\VatsimData::__construct
     */
    public function testConstruct()
    {
        $data = $this->getDataMock();
        $this->assertNotEmpty($data->getConfig('logFile'));
        $this->assertNotEmpty($data->getConfig('cacheDir'));
    }

    /**
     * @param array $setMethods To be passed into setMethods mock builder
     *
     * @return Vatsimphp\VatsimData
     */
    protected function getDataMock($setMethods = null)
    {
        return $this->getMockBuilder('Vatsimphp\VatsimData')
            ->setMethods($setMethods)
            ->getMock();
    }

    /**
     * @return Vatsimphp\Result\ResultContainer
     */
    protected function getContainerMock($setMethods = null)
    {
        return $this->getMockBuilder('Vatsimphp\Result\ResultContainer')
            ->setMethods($setMethods)
            ->getMock();
    }

    /**
     * Attach result container to data object.
     *
     * @param Vatsimphp\VatsimData             $data
     * @param Vatsimphp\Result\ResultContainer $container
     *
     * @return Vatsimphp\VatsimData
     */
    protected function attachContainer(VatsimData $data, ResultContainer $container)
    {
        $result = new \ReflectionProperty($data, 'results');
        $result->setAccessible(true);
        $result->setValue($data, $container);

        return $data;
    }

    /**
     * Return Sync mock.
     *
     * @param string $name
     *
     * @return Vatsimphp\Sync\AbstractSync
     */
    protected function getSyncMock($name)
    {
        $class = 'Vatsimphp\\Sync\\'.ucfirst($name).'Sync';
        $sync = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        return $sync;
    }
}
