<?php

/*
 * This file is part of the Vatsimphp package
 *
 * Copyright 2018 - Jelle Vink <jelle.vink@gmail.com>
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
 */

namespace Vatsimphp\Filter;

/**
 * Filter class to get specific variables like:.
 *
 * url0=http://www.pcflyer.net/DataFeed/vatsim-data.txt
 * url0=http://www.klain.net/sidata/vatsim-data.txt
 * ...
 */
class VarFilter extends StartOfLineFilter
{
    /**
     * @see Vatsimphp\Filter.AbstractFilter::setFilter()
     */
    public function setFilter($filter)
    {
        $this->filter = "{$filter}=";
    }

    /**
     * @see Vatsimphp\Filter.AbstractFilter::current()
     */
    public function current()
    {
        $value = parent::current();

        return trim(substr($value, strlen($this->filter)));
    }
}
