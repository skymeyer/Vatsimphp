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

use PHPUnit\Framework\TestCase;

class AbstractSyncTest extends TestCase
{
    protected function setUp()
    {
        $dir = 'build/tests';
        @mkdir($dir, 0777, true);
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
        @unlink($dir.'/savetocache.test');
        @unlink($dir.'/expire.test');
    }

    /**
     * Test inheritance.
     */
    public function testImplements()
    {
        $class = $class = $this->getMockBuilder('Vatsimphp\Sync\AbstractSync')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assertInstanceOf('Vatsimphp\Sync\SyncInterface', $class);
    }

    /**
     * Test default settings.
     *
     * @covers Vatsimphp\Sync\AbstractSync::__construct
     */
    public function testDefaults()
    {
        $class = $this->getMockAbstractySync();

        // protected urls
        $propUrls = new \ReflectionProperty($class, 'urls');
        $propUrls->setAccessible(true);
        $this->assertSame([], $propUrls->getValue($class));

        // public properties
        $this->assertFalse($class->forceRefresh);
        $this->assertFalse($class->cacheOnly);
    }

    /**
     * Test setting parser.
     *
     * @covers Vatsimphp\Sync\AbstractSync::setParser
     */
    public function testSetParser()
    {
        $class = $this->getMockAbstractySync();
        $class->setParser('Data');
        $parser = new \ReflectionProperty($class, 'parser');
        $parser->setAccessible(true);
        $this->assertInstanceOf(
            'Vatsimphp\Parser\DataParser',
            $parser->getValue($class)
        );
    }

    /**
     * Test setting invalid parser.
     *
     * @covers Vatsimphp\Sync\AbstractSync::setParser
     * @expectedException Vatsimphp\Exception\RuntimeException
     */
    public function testSetParserInvalid()
    {
        $class = $this->getMockAbstractySync();
        $class->setParser('bogus');
    }

    /**
     * Test url setter/getter.
     *
     * @dataProvider providerTestRegisterUrl
     * @covers Vatsimphp\Sync\AbstractSync::registerUrl
     * @covers Vatsimphp\Sync\AbstractSync::getUrls
     */
    public function testRegisterUrl($data, $expectedResult)
    {
        $class = $this->getMockAbstractySync();

        // set base urls for flush test
        $base = ['http://link0'];
        $urlProp = new \ReflectionProperty($class, 'urls');
        $urlProp->setAccessible(true);
        $urlProp->setValue($class, $base);

        // noflush
        $class->registerUrl($data, false);
        $expectedMerge = array_merge($base, $expectedResult);
        $this->assertSame($expectedMerge, $class->getUrls());

        // flush
        $urlProp->setValue($class, $base);
        $class->registerUrl($data, true);
        $this->assertSame($expectedResult, $class->getUrls());
    }

    public function providerTestRegisterUrl()
    {
        return [
            [
                'http://link1',
                ['http://link1'],
            ],
            [
                ['http://link1'],
                ['http://link1'],
            ],
            [
                ['http://link1', 'http://link2'],
                ['http://link1', 'http://link2'],
            ],
        ];
    }

    /**
     * Curl resource test.
     *
     * @covers Vatsimphp\Sync\AbstractSync::initCurl
     */
    public function testInitCurl()
    {
        $class = $this->getMockAbstractySync();
        $init = new \ReflectionMethod($class, 'initCurl');
        $init->setAccessible(true);
        $init->invoke($class, []);

        $curl = new \ReflectionProperty($class, 'curl');
        $curl->setAccessible(true);
        $this->assertNotEmpty($curl->getValue($class));
    }

    /**
     * Test get data.
     *
     * @covers Vatsimphp\Sync\AbstractSync::getData
     */
    public function testGetData()
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\AbstractSync')
            ->setMethods(['loadFromUrl', 'loadFromCache'])
            ->getMockForAbstractClass();

        // stub loadFromUrl
        $class->expects($this->any())
            ->method('loadFromUrl')
            ->will($this->returnValue('fromUrl'));

        // stub loadFromCache
        $class->expects($this->any())
            ->method('loadFromCache')
            ->will($this->returnValue('fromCache'));

        $getData = new \ReflectionMethod($class, 'getData');
        $getData->setAccessible(true);

        $this->assertEquals('fromUrl', $getData->invoke($class, 'http://link'));
        $this->assertEquals('fromUrl', $getData->invoke($class, 'https://link'));
        $this->assertEquals('fromCache', $getData->invoke($class, 'local/file'));
    }

    /**
     * Test valid data report from parser.
     *
     * @covers Vatsimphp\Sync\AbstractSync::isDataValid
     */
    public function testIsDataValid()
    {
        $parser = $this->getMockBuilder('Vatsimphp\Parser\AbstractParser')
            ->setMethods(['isValid'])
            ->getMockForAbstractClass();

        // stub valid check
        $parser->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        $class = $this->getMockAbstractySync();

        // attach mocked parser
        $parserProp = new \ReflectionProperty($class, 'parser');
        $parserProp->setAccessible(true);
        $parserProp->setValue($class, $parser);

        $isValid = new \ReflectionMethod($class, 'isDataValid');
        $isValid->setAccessible(true);

        $this->assertTrue($isValid->invoke($class, 'bogusdata'));
    }

    /**
     * Test save cache file content.
     *
     * @dataProvider providerTestSaveToCache
     * @covers Vatsimphp\Sync\AbstractSync::saveToCache
     */
    public function testSaveToCache($data)
    {
        $class = $this->getMockAbstractySync();

        $testFile = 'build/tests/savetocache.test';
        $filePath = new \ReflectionProperty($class, 'filePath');
        $filePath->setAccessible(true);
        $filePath->setValue($class, $testFile);

        $save = new \ReflectionMethod($class, 'saveToCache');
        $save->setAccessible(true);

        foreach ($data as $content) {
            $save->invoke($class, $content);
            $this->assertEquals($content, file_get_contents($testFile));
        }
    }

    public function providerTestSaveToCache()
    {
        return [
            [['save1', 'save2']],
        ];
    }

    /**
     * Test cache expired.
     *
     * @dataProvider providerTestIsCacheExpired
     * @covers Vatsimphp\Sync\AbstractSync::isCacheExpired
     */
    public function testIsCacheExpired($refreshInterval, $cacheOnly, $status)
    {
        // test file
        $testFile = 'build/tests/expire.test';
        file_put_contents($testFile, 'bogus');

        $class = $this->getMockAbstractySync();

        // set filePath
        $filePath = new \ReflectionProperty($class, 'filePath');
        $filePath->setAccessible(true);
        $filePath->setValue($class, $testFile);

        // set refresh
        $refresh = new \ReflectionProperty($class, 'refreshInterval');
        $refresh->setAccessible(true);
        $refresh->setValue($class, $refreshInterval);

        // set cacheOnly
        $cache = new \ReflectionProperty($class, 'cacheOnly');
        $cache->setAccessible(true);
        $cache->setValue($class, $cacheOnly);

        // isCacheExpired
        $expired = new \ReflectionMethod($class, 'isCacheExpired');
        $expired->setAccessible(true);

        $this->assertSame($status, $expired->invoke($class));
    }

    public function providerTestIsCacheExpired()
    {
        return [
            [0, false, true],
            [30, false, false],
            [0, true, false],
            [30, true, false],
        ];
    }

    /**
     * Test config validation (valid).
     *
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
        $class = $this->getMockAbstractySync();

        // prepare test values
        foreach ($data as $property => $value) {
            $propReflect = new \ReflectionProperty($class, $property);
            $propReflect->setAccessible(true);
            $propReflect->setValue($class, $value);
        }

        $sut = new \ReflectionMethod($class, 'validateConfig');
        $sut->setAccessible(true);
        $this->assertTrue($sut->invoke($class, []));
    }

    public function providerTestValidateConfig()
    {
        $parser = $this->getMockBuilder('Vatsimphp\Parser\AbstractParser')
            ->getMockForAbstractClass();

        return [
            [
                [
                    'urls'            => ['http://link'],
                    'refreshInterval' => 30,
                    'cacheFile'       => 'notempty',
                    'filePath'        => 'build/tests/writeable.test',
                    'parser'          => $parser,
                ],
            ],
            [
                [
                    'urls'            => ['http://link'],
                    'refreshInterval' => 30,
                    'cacheFile'       => 'notempty',
                    'filePath'        => 'build/tests/newwriteable.test',
                    'parser'          => $parser,
                ],
            ],
        ];
    }

    /**
     * Test config validation - UnexpectedValueException.
     *
     * @dataProvider providerTestValidateConfigUnexpectedValue
     * @covers Vatsimphp\Sync\AbstractSync::validateUrls
     * @covers Vatsimphp\Sync\AbstractSync::validateRefreshInterval
     * @covers Vatsimphp\Sync\AbstractSync::validateCacheFile
     * @expectedException Vatsimphp\Exception\UnexpectedValueException
     */
    public function testValidateConfigUnexpectedValue($property, $value)
    {
        $class = $this->getMockAbstractySync();

        // access protected property
        $propReflect = new \ReflectionProperty($class, $property);
        $propReflect->setAccessible(true);
        $propReflect->setValue($class, $value);

        // access protected method
        $validationMethod = 'validate'.ucfirst($property);
        $sut = new \ReflectionMethod($class, $validationMethod);
        $sut->setAccessible(true);
        $sut->invoke($class, []);
    }

    public function providerTestValidateConfigUnexpectedValue()
    {
        return [
            ['urls', null],
            ['refreshInterval', 'bogus'],
            ['cacheFile', ''],
        ];
    }

    /**
     * Test config validation - RuntimeException.
     *
     * @dataProvider providerTestValidateConfigRuntime
     * @covers Vatsimphp\Sync\AbstractSync::validateFilePath
     * @covers Vatsimphp\Sync\AbstractSync::validateParser
     * @expectedException Vatsimphp\Exception\RuntimeException
     */
    public function testValidateConfigRuntime($property, $value)
    {
        $class = $this->getMockAbstractySync();

        // access protected property
        $propReflect = new \ReflectionProperty($class, $property);
        $propReflect->setAccessible(true);
        $propReflect->setValue($class, $value);

        // access protected method
        $validationMethod = 'validate'.ucfirst($property);
        $sut = new \ReflectionMethod($class, $validationMethod);
        $sut->setAccessible(true);
        $sut->invoke($class, []);
    }

    public function providerTestValidateConfigRuntime()
    {
        return [
            ['filePath', '/bogus/noexist.test'],
            ['filePath', 'build/tests/unwriteable.test'],
            ['parser', null],
        ];
    }

    /**
     * Test load data - no locations available.
     *
     * @covers Vatsimphp\Sync\AbstractSync::loadData
     * @expectedException Vatsimphp\Exception\SyncException
     * @expectedExceptionMessage No location(s) available to sync from
     */
    public function testLoadDataNoLocations()
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\AbstractSync')
            ->setMethods(['validateConfig'])
            ->getMockForAbstractClass();

        $class->cacheDir = 'build/tests';
        $class->cacheFile = 'notexists';
        $class->loadData();
    }

    /**
     * Test load data - no valid data.
     *
     * @covers Vatsimphp\Sync\AbstractSync::loadData
     * @expectedException Vatsimphp\Exception\SyncException
     * @expectedExceptionMessage Unable to download data or data invalid
     */
    public function testLoadDataNoDownload()
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\AbstractSync')
            ->setMethods(['validateConfig', 'getData'])
            ->getMockForAbstractClass();

        $parser = $this->getMockBuilder('Vatsimphp\Parser\AbstractParser')
            ->setMethods(['isValid'])
            ->getMockForAbstractClass();

        $parserProp = new \ReflectionProperty($class, 'parser');
        $parserProp->setAccessible(true);
        $parserProp->setValue($class, $parser);

        $class->cacheDir = 'build/tests';
        $class->cacheFile = 'notexists';
        $class->registerUrl('http://link');
        $class->loadData();
    }

    /**
     * Test load data.
     *
     * @dataProvider providerTestLoadData
     * @covers Vatsimphp\Sync\AbstractSync::loadData
     */
    public function testLoadData($cacheDir, $cacheFile, $urls)
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\AbstractSync')
            ->setMethods(['validateConfig', 'getData'])
            ->getMockForAbstractClass();

        // stub getData
        $class->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(true));

        // parser mock
        $parser = $this->getMockBuilder('Vatsimphp\Parser\AbstractParser')
            ->setMethods(['isValid', 'getParsedData'])
            ->getMockForAbstractClass();

        // stub isValid
        $parser->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        // stub getParsedData
        $parser->expects($this->any())
            ->method('getParsedData')
            ->will($this->returnValue(true));

        // attach parser
        $parserProp = new \ReflectionProperty($class, 'parser');
        $parserProp->setAccessible(true);
        $parserProp->setValue($class, $parser);

        $class->cacheDir = $cacheDir;
        $class->cacheFile = $cacheFile;
        $class->registerUrl($urls, true);

        $this->assertTrue($class->loadData());
    }

    public function providerTestLoadData()
    {
        return [
            ['build/tests', 'notexist', ['http://link']],
            ['build/tests', 'notexist', ['http://link1', 'http://link2']],
            ['build/tests', 'writeable.test', ['http://link1', 'http://link2']],
        ];
    }

    /**
     * Test prepare urls.
     *
     * @dataProvider providerTestPrepareUrls
     * @covers Vatsimphp\Sync\AbstractSync::prepareUrls
     */
    public function testPrepareUrls($filePath, $urls, $refresh, $cacheOnly, $expectedResult)
    {
        $class = $this->getMockAbstractySync();
        $prepare = new \ReflectionMethod($class, 'prepareUrls');
        $prepare->setAccessible(true);
        $result = $prepare->invoke($class, $filePath, $urls, $refresh, $cacheOnly, false);
        $this->assertSame($expectedResult, $result);
    }

    public function providerTestPrepareUrls()
    {
        return [
            // invalid cache file, one link
            [
                'build/tests/notexist',
                ['http://link'],
                false,
                false,
                ['http://link'],
            ],
            // invalid cache file, multi link
            [
                'build/tests/notexist',
                ['http://link1', 'http://link2'],
                false,
                false,
                ['http://link1', 'http://link2'],
            ],
            // valid cache file, multi link
            [
                'build/tests/writeable.test',
                ['http://link1', 'http://link2'],
                false,
                false,
                ['build/tests/writeable.test', 'http://link1', 'http://link2'],
            ],
            // valid cache file, multi link - forceRefresh
            [
                'build/tests/writeable.test',
                ['http://link1', 'http://link2'],
                true,
                false,
                ['http://link1', 'http://link2'],
            ],
            // valid cache file, multi link - cacheOnly
            [
                'build/tests/writeable.test',
                ['http://link1', 'http://link2'],
                false,
                true,
                ['build/tests/writeable.test'],
            ],
            // valid cache file, multi link - forceRefresh/cacheOnly
            [
                'build/tests/writeable.test',
                ['http://link1', 'http://link2'],
                true,
                true,
                ['build/tests/writeable.test'],
            ],
            // invalid cache file, multi link - cacheOnly
            [
                'build/tests/notexist',
                ['http://link1', 'http://link2'],
                false,
                true,
                [],
            ],
            // invalid cache file, multi link - forceRefresh/cacheOnly
            [
                'build/tests/notexist',
                ['http://link1', 'http://link2'],
                true,
                true,
                [],
            ],
        ];
    }

    /**
     * Test load from url.
     *
     * @dataProvider providerTestLoadFromUrl
     * @covers Vatsimphp\Sync\AbstractSync::loadFromUrl
     * @covers Vatsimphp\Sync\AbstractSync::getErrors
     * @covers Vatsimphp\Sync\AbstractSync::addError
     */
    public function testLoadFromUrl($curlData, $valid, $expected, $errorCount)
    {
        $mockedMethods = [
            'getDataFromCurl',
            'isDataValid',
            'saveToCache',
        ];
        $sync = $this->getMockAbstractySync($mockedMethods);

        // mock getDataFromCurl
        $sync->expects($this->once())
            ->method('getDataFromCurl')
            ->will($this->returnValue($curlData));

        // mock isDataValid
        $sync->expects($this->any())
            ->method('isDataValid')
            ->will($this->returnValue($valid));

        // mock saveToCache
        $sync->expects($this->any())
            ->method('saveToCache')
            ->with($this->equalTo($curlData));

        // execute test
        $reflection = new \ReflectionMethod($sync, 'loadFromUrl');
        $reflection->setAccessible(true);
        $this->assertEquals($expected, $reflection->invoke($sync, 'http://bogus'));
        $this->assertCount($errorCount, $sync->getErrors());
    }

    public function providerTestLoadFromUrl()
    {
        return [
            ['dataok', true, true, 0],
            [false, false, false, 0], // error count still 0 because mocked curl
            ['dataok', false, false, 1],
        ];
    }

    /**
     * Test load from cache.
     *
     * @dataProvider providerTestLoadFromCache
     * @covers Vatsimphp\Sync\AbstractSync::loadFromCache
     * @covers Vatsimphp\Sync\AbstractSync::getErrors
     */
    public function testLoadFromCache($fileData, $valid, $expired, $expected, $errorCount)
    {
        $mockedMethods = [
            'getDataFromFile',
            'isDataValid',
            'isCacheExpired',
        ];
        $sync = $this->getMockAbstractySync($mockedMethods);

        // mock getDataFromFile
        $sync->expects($this->once())
            ->method('getDataFromFile')
            ->will($this->returnValue($fileData));

        // mock isDataValid
        $sync->expects($this->once())
            ->method('isDataValid')
            ->will($this->returnValue($valid));

        // mock isCacheExpired
        $sync->expects($this->any())
            ->method('isCacheExpired')
            ->will($this->returnValue($expired));

        // execute test
        $reflection = new \ReflectionMethod($sync, 'loadFromCache');
        $reflection->setAccessible(true);
        $this->assertEquals($expected, $reflection->invoke($sync));
        $this->assertCount($errorCount, $sync->getErrors());
    }

    public function providerTestLoadFromCache()
    {
        return [
            ['dataok',  true, false,  true, 0], // normal
            ['dataok',  true,  true, false, 1], // expired valid content
            [false,    false, false, false, 1], // not valid not expired
            [false,    false,  true, false, 1], // not valid and expired
        ];
    }

    /**
     * Override url provider for extension classes.
     *
     * @covers Vatsimphp\Sync\AbstractSync::overrideUrl
     */
    public function testOverrideUrl()
    {
        $sync = $this->getMockAbstractySync();
        $override = new \ReflectionMethod($sync, 'overrideUrl');
        $override->setAccessible(true);
        $this->assertEquals('doingnothing', $override->invoke($sync, 'doingnothing'));
    }

    /**
     * Return mocked AbstractSync.
     */
    protected function getMockAbstractySync($setMethods = [])
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\AbstractSync')
            ->setMethods($setMethods)
            ->getMockForAbstractClass();

        return $class;
    }
}
