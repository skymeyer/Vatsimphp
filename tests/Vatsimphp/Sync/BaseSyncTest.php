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

class BaseSyncTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * Test inheritance
     */
    public function testImplements()
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\BaseSync')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Sync\AbstractSync', $class);
    }

    /**
     *
     * Test url registration through status object
     * @dataProvider providerTestRegisterUrlFromStatus
     * @covers Vatsimphp\Sync\BaseSync::registerUrlFromStatus
     */
    public function testRegisterUrlFromStatus($dataName, $data)
    {
        $class = $this->getMockBaseSync();
        $status = $this->getMockStatusSync($dataName, $data);
        $this->assertTrue($class->registerUrlFromStatus($status, $dataName));
        $this->assertSame($data, $class->getUrls());
    }

    public function providerTestRegisterUrlFromStatus()
    {
        return array(
            array(
                'url0',
                array(
                    'http://link1',
                ),
                'url1',
                array(
                    'http://link1',
                    'http://link2',
                ),
            ),
        );
    }

    /**
     *
     * Test url registration through status object
     * @covers Vatsimphp\Sync\BaseSync::registerUrlFromStatus
     * @expectedException Vatsimphp\Exception\RuntimeException
     * @expectedExceptionMessage Error loading urls from StatusSync
     */
    public function testRegisterUrlFromStatusFailure()
    {
        $class = $this->getMockBaseSync();
        $status = $this->getMockStatusSync();
        $class->registerUrlFromStatus($status, 'bogus');
    }

    /**
     *
     * Return mock for abstract BaseSync
     */
    protected function getMockBaseSync()
    {
        $class = $this->getMockBuilder('Vatsimphp\Sync\BaseSync')
            ->getMockForAbstractClass();
        return $class;
    }

    /**
     *
     * Get mocked StatusSync
     */
    protected function getMockStatusSync($dataName = false, $data = null)
    {
        $status = $this->getMockBuilder('Vatsimphp\Sync\StatusSync')
            ->disableOriginalConstructor()
            ->setMethods(array('loadData'))
            ->getMock();

        // mocked result set
        $resultSet = $this->getMockBuilder('Vatsimphp\Result\ResultContainer')
            ->setMethods(null)
            ->getMock();

        // append results
        if ($dataName) {
            $resultSet->append($dataName, $data);
        }

        // stub loadData to return resultSet
        $status->expects($this->any())
            ->method('loadData')
            ->will($this->returnValue($resultSet));

        return $status;
    }
}
