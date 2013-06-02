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

use Vatsimphp\Sync\StatusSync;
use Vatsimphp\Sync\DataSync;

/**
 *
 * Main consumer class to retrieve and
 * query data from the VATSIM network
 *
 */
class VatsimData
{
    /**
     *
     * Default configuration
     * @var array
     */
    protected $config = array(
        'cacheDir' => '.',
        'statusUrl' => '',
        'statusRefresh' => 86400,
        'dataRefresh' => 180,
        'dataExpire' => 3600,
        'forceRefresh' => false,
        'cacheOnly' => false,
    );

    /**
     *
     * Result iterator
     * @var \Vatsimphp\Result\ResultContainer
     */
    protected $results;

    /**
     *
     * Exception stack
     * @var array
     */
    protected $exceptionStack = array();

    /**
     *
     * Search for a callsign
     * @param string $callsign
     */
    public function searchCallsign($callsign)
    {
        return $this->search('clients', array('callsign' => $callsign));
    }

    /**
     *
     * Expose search on result container
     * @param string $objectType
     * @param array $query
     */
    public function search($objectType, $query)
    {
        return $this->results->search($objectType, $query);
    }

    /**
     *
     * Override default config settings
     * @param string $key
     * @param mixed(string|boolean|integer) $value
     */
    public function setConfig($key, $value)
    {
        if (isset($this->config[$key])) {
            $this->config[$key] = $value;
        }
    }

    /**
     *
     * Return config key
     * @param string $key
     * @return mixed(string|boolean|integer)
     */
    public function getConfig($key)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key] = $value;
        }
    }

    /**
     *
     * Load data from Vatsim network
     * @return boolean
     */
    public function loadData()
    {
        $status = new StatusSync();
        $status->cacheDir = $this->config['cacheDir'];
        $status->refreshInterval = $this->config['statusRefresh'];

        if (!empty($this->config['statusUrl'])) {
            $status->registerUrl($this->config['statusUrl'], true);
        }

        try {
            $data = new DataSync();
            $data->cacheDir = $this->config['cacheDir'];
            $data->dataExpire = $this->config['dataExpire'];
            $data->refreshInterval = $this->config['dataRefresh'];
            $data->forceRefresh = $this->config['forceRefresh'];
            $data->cacheOnly = $this->config['cacheOnly'];

            // auto register urls from status
            $data->registerUrlFromStatus($status, 'dataUrls');
            $this->results = $data->loadData();
        } catch (Exception $e) {
            $this->exceptionStack[] = $e;
            return false;
        }
        return true;
    }

    /**
     *
     * Wrapper returning available data objects
     * @return array
     */
    public function getObjectTypes()
    {
        return $this->results->getList();
    }

    /**
     *
     * Overload method to access data objects directly
     * @param string $objectType
     * @return \Vatsimphp\Filter\Iterator
     */
    public function __get($objectType)
    {
        return $this->getIterator($objectType);
    }

    /**
     *
     * Access data object
     * @param string $objectType
     * @return array
     */
    public function getArray($objectType)
    {
        return $this->getIterator($objectType)->toArray();
    }

    /**
     *
     * Access data object
     * @param string $objectType
     * @return \Vatsimphp\Filter\Iterator
     */
    public function getIterator($objectType)
    {
        return $this->results->get($objectType);
    }
}
