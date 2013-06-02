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

class AbstractParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Parser\AbstractParser')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assertInstanceOf('Vatsimphp\Parser\ParserInterface', $class);
    }

    /**
     *
     * Test default settings
     * @covers Vatsimphp\Parser\AbstractParser::__construct
     */
    public function testDefaults()
    {
        $class = $this->getMockBuilder('Vatsimphp\Parser\AbstractParser')
            ->getMockForAbstractClass();

        // protected valid
        $propValid = new \ReflectionProperty($class, 'valid');
        $propValid->setAccessible(true);
        $this->assertFalse($propValid->getValue($class));

        // protected log
        $propLog = new \ReflectionProperty($class, 'log');
        $propLog->setAccessible(true);
        $this->assertInstanceOf('Psr\Log\LoggerInterface', $propLog->getValue($class));

        // protected results
        $propLog = new \ReflectionProperty($class, 'results');
        $propLog->setAccessible(true);
        $this->assertInstanceOf('Vatsimphp\Result\ResultContainer', $propLog->getValue($class));

    }

    /**
     *
     * @covers Vatsimphp\Parser\AbstractParser::setData
     * @covers Vatsimphp\Parser\AbstractParser::getHash
     * @covers Vatsimphp\Parser\AbstractParser::getRawData
     * @covers Vatsimphp\Parser\AbstractParser::isValid
     * @covers Vatsimphp\Parser\AbstractParser::getParsedData
     */
    public function testGettersSetters()
    {
        $class = $this->getMockBuilder('Vatsimphp\Parser\AbstractParser')
            ->getMockForAbstractClass();

        $data = 'line1'.PHP_EOL.'line2'.PHP_EOL.'line3';
        $expectedData = array(
            'line1', 'line2', 'line3',
        );
        $class->setData($data);

        $this->assertEquals(md5($data, false), $class->getHash());
        $this->assertSame($expectedData, $class->getRawData());
        $this->assertFalse($class->isValid());
        $this->assertInstanceOf('Vatsimphp\Result\ResultContainer', $class->getParsedData());
        $this->assertCount(0, $class->getParsedData());

    }
}
