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

namespace Vatsimphp\Result;

use Vatsimphp\Filter\Iterator;

/**
 *
 * Result Container used to register all iterators
 * returned by the parsers. This will be the main
 * backend object to source/query data from.
 *
 */
class ResultContainer implements \Countable
{
    /**
     *
     * Results container
     * @var array
     */
    protected $container = array();

    /**
     *
     * Ctor
     */
    public function __construct()
    {
    }

    /**
     *
     * Append/overwrite a new result set
     * @param string $name
     * @param array|\Vatsimphp\Filter\AbstractFilter $data
     */
    public function append($name, $data)
    {
        if (!$data instanceof \Vatsimphp\Filter\AbstractFilter) {
            $data = new Iterator($data);
        }
        $name = str_replace(' ', '_', $name);
        $this->container[$name] = $data;
    }

    /**
     *
     * Overload method for direct result object access
     * @param string $name
     * @return \Vatsimphp\Filter\AbstractFilter
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     *
     * Get result object from container
     * @param string $name
     * @return \Vatsimphp\Filter\AbstractFilter
     */
    public function get($name)
    {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        } else {
            return new Iterator(array());
        }
    }

    /**
     *
     * List registered result names
     * @return array
     */
    public function getList()
    {
        return array_keys($this->container);
    }

    /**
     *
     * @see Countable::count()
     */
    public function count()
    {
        return count($this->container);
    }

    /**
     *
     * Base search functionality
     * @param string $objectType
     * @param array $query
     * @return \Vatsimphp\Filter\Iterator
     */
    public function search($objectType, $query = array())
    {
        $results = array();
        if ($this->isSearchable($objectType)) {
            foreach ($this->get($objectType) as $line) {
                foreach ($query as $field => $needle) {
                    if (isset($line[$field])) {
                        if (stripos($line[$field], $needle) !== false) {
                            $results[] = $line;
                        }
                    }
                }
            }
        }
        return new Iterator($results);
    }

    /**
     *
     * Check if given result set is searchable. To be true
     * a matching header result set is required.
     * @param string $objectType
     * @return boolean
     */
    protected function isSearchable($objectType)
    {
        return (boolean)count($this->get("{$objectType}_header"));
    }
}
