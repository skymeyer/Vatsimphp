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

namespace Vatsimphp\Parser;

use Vatsimphp\Result\ResultContainer;
use Vatsimphp\Log\LoggerFactory;

/**
 *
 * Abstract class for data parsers
 *
 */
abstract class AbstractParser implements ParserInterface
{
    /**
     *
     * Raw data
     * @var array
     */
    protected $rawData;

    /**
     *
     * Flag to indicate valid data
     * @var boolean
     */
    protected $valid = false;

    /**
     *
     * Results
     * @var \Vatsimphp\Result\ResultContainer
     */
    protected $results;

    /**
     *
     * Version hash of raw data
     * @var string
     */
    protected $hash;

    /**
     *
     * Logger
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

    /**
     *
     * Ctor
     */
    public function __construct()
    {
        $this->results = new ResultContainer();
        $this->log = LoggerFactory::get($this);
    }

    /**
     *
     * Set raw data
     * @param string $data
     */
    public function setData($data)
    {
        $this->valid = false;
        $this->hash = md5($data, false);
        $this->rawData = explode("\n", $data);
    }

    /**
     *
     * Return validation state
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     *
     * Return parsed data
     * @return \Vatsimphp\Result\ResultContainer
     */
    public function getParsedData()
    {
        return $this->results;
    }

    /**
     *
     * Return raw data
     * @return array
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     *
     * Return the hash (version)
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
}
