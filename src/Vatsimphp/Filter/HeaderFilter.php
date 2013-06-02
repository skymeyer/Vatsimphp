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

namespace Vatsimphp\Filter;

/**
 *
 * Filter iterator to retrieve the field header information
 * from a vatsim-data source like:
 *
 * !CLIENTS section - callsign:cid:realname:...
 *
 * Those data headers are used in the result sets and used
 * by the query functionality.
 *
 */
class HeaderFilter extends StartOfLineFilter
{
    /**
     *
     * @see Vatsimphp\Filter.AbstractFilter::setFilter()
     */
    public function setFilter($section)
    {
        $this->skipComments = false;
        $this->filter = "; !".strtoupper($section)." section - ";
    }

    /**
     *
     * @see Vatsimphp\Filter.AbstractFilter::current()
     */
    public function current()
    {
        $value = trim(substr(parent::current(), strlen($this->filter)));
        return $this->convertToArray($value, ':');
    }
}
