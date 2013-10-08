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

use Vatsimphp\Filter\HeaderFilter;
use Vatsimphp\Filter\SectionDataFilter;
use Vatsimphp\Filter\SectionGeneralFilter;

/**
 *
 * Parser for vatsim-data.txt
 *
 */
class DataParser extends AbstractParser
{
    /**
     *
     * Expire time in seconds to consider data file timestamp
     * invalid. Set to zero to disable this check.
     * @var integer
     */
    public $dataExpire = 0;

    /**
     *
     * Parsable sections in the data file
     * @var array
     */
    protected $sections = array(
        'clients' => false,
        'prefile' => false,
        'servers' => false,
        'voice servers' => false,
    );

    /**
     *
     * Key/value pairs in the general section
     * @var array
     */
    protected $general = array(
        'version' => false,
        'reload' => false,
        'update' => false,
        'atis_allow_min' => false,
        'connected_clients' => false,
    );

    /**
     *
     * @see Vatsimphp\Parser.ParserInterface::parseData()
     */
    public function parseData()
    {
        // parse data sections
        $this->parseSections();
        $this->parseGeneral();

        // append raw data too
        $this->results->append('raw', $this->rawData);

        // valid if we have a valid update timestamp
        if ($this->general['update']) {
            $this->valid = true;
            $this->log->debug("Valid data with timestamp {$this->general['update']}");
        }

        // optional check on actual data timestamp
        if ($this->timestampHasExpired($this->general['update'], $this->dataExpire)) {
            $this->log->debug("Data with timestamp {$this->general['update']} has expired");
            $this->valid = false;
        }
    }

    /**
     *
     * Check if given timestamp is expired
     * @param integer $ts
     * @return boolean
     */
    protected function timestampHasExpired($ts, $expire)
    {
        if (empty($expire)) {
            return false;
        }
        $diff = time() - $ts;
        if ($diff > $expire) {
            return true;
        }
        return false;
    }

    /**
     *
     * Parse data sections
     */
    protected function parseSections()
    {
        foreach (array_keys($this->sections) as $section) {

            // section headers
            $header = new HeaderFilter($this->rawData);
            $header->setFilter($section);
            $headerData = $header->toArray();
            $headerData = array_shift($headerData);

            // skip section if no headers found
            if (empty($headerData)) {
                $this->log->debug("No header for section $section");
                continue;
            }

            // save header separately
            $this->results->append(
                $this->scrubKey("{$section}_header"),
                $headerData
            );

            // section data
            $data = new SectionDataFilter($this->rawData);
            $data->setFilter($section);
            $data->setHeader($headerData);
            $this->results->append(
                $this->scrubKey($section),
                $data
            );
            $this->log->debug("Parsed section '{$this->scrubKey($section)}'");
        }
    }

    /**
     *
     * Parse GENERAL section parameters and transform
     * them into proper key/value pairs
     */
    protected function parseGeneral()
    {
        // load data
        $data = new SectionGeneralFilter($this->rawData);

        foreach ($data as $entry) {

            // grab key/values from iterator
            $params = explode(" = ", $entry);
            if (count($params) != 2) {
                continue;
            }

            // map key/values
            $genKey = $this->scrubKey($params[0]);
            $genVal = $params[1];
            if (isset($this->general[$genKey])) {
                $this->general[$genKey] = $genVal;
                $this->log->debug("General section: $genKey -> $genVal");
            }
        }

        // convert date to unix timestamp
        $this->general['update'] = $this->convertTs($this->general['update']);
        $this->results->append('general', $this->general);
    }

    /**
     *
     * Array key formatter
     * @param  string $key
     * @return string
     */
    protected function scrubKey($key)
    {
        return str_replace(' ', '_', strtolower($key));
    }

    /**
     *
     * Timestamp convertor to unix time
     * @param  string $str
     * @return integer
     */
    protected function convertTs($str)
    {
        if (empty($str) || strlen($str) != 14) {
            return false;
        }
        $y = (int)substr($str, 0, 4);
        $m = (int)substr($str, 4, 2);
        $d = (int)substr($str, 6, 2);
        $h = (int)substr($str, 8, 2);
        $i = (int)substr($str, 10, 2);
        $s = (int)substr($str, 12, 2);

        $dt = new \DateTime();
        $dt->setTimezone(new \DateTimeZone('UTC'));
        $dt->setDate($y, $m, $d);
        $dt->setTime($h, $i, $s);
        return $dt->getTimestamp();
    }
}
