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

namespace Vatsimphp\Exception;

/**
 *
 * Synchronisation exception
 *
 */
class SyncException extends \RuntimeException implements ExceptionInterface
{
    /**
     *
     * Errors
     * @var array
     */
    protected $errors = array();

    /**
     *
     * Ctor
     * @param string $msg
     * @param array $errors
     */
    public function __construct($msg, array $errors)
    {
        $this->errors = $errors;
        parent::__construct($msg);
    }

    /**
     *
     * Return reported errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
