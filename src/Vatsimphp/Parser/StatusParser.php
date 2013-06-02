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

use Vatsimphp\Filter\VarFilter;

/**
 *
 * Parser for status.txt:
 * Contains all published urls from the VATSIM network to gather info
 *
 */
class StatusParser extends AbstractParser
{
    /**
     *
     * Possible published url endpoints
     * @var array
     */
    protected $endpoints = array(
        'url0' => 'dataUrls',
        'url1' => 'serverUrls',
        'metar0' => 'metarUrls',
        'atis0' => 'atisUrls',
    );

    /**
     *
     * @see Vatsimphp\Parser.ParserInterface::parseData()
     */
    public function parseData()
    {
        // parse possible endpoints
        foreach ($this->endpoints as $var => $target) {
            $data = new VarFilter($this->rawData);
            $data->setFilter($var);
            $this->results->append($target, $data);
        }

        // validate info if at least one dataUrl is present
        if (count($this->results->get('dataUrls')->toArray())) {
            $this->log->debug("Data validated - dataUrls available");
            $this->valid = true;
        }
    }
}
