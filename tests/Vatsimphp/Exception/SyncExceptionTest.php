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

class SyncExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testSyncException()
    {
        $msg = 'something went wrong';
        $errors = ['err1' => 'doh1', 'err2' => 'doh2'];
        $exception = $this->getMockBuilder('Vatsimphp\Exception\SyncException')
            ->setConstructorArgs([$msg, $errors])
            ->setMethods(null)
            ->getMock();
        $this->assertInstanceOf('Vatsimphp\Exception\ExceptionInterface', $exception);
        $this->assertSame($msg, $exception->getMessage());
        $this->assertSame($errors, $exception->getErrors());
    }
}
