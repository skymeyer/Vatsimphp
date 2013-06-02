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
 * Filter to get data values for a specific section like:
 *
 * SWA3437:1141672:Jelle Vink KSJC:PILOT:...
 *
 */
class SectionDataFilter extends SectionFilter
{
    /**
     *
     * Header to map the results to
     * @var array
     */
    protected $header = array();

    /**
     *
     * Set header to be applied
     * @param array $header
     */
    public function setHeader(Array $header)
    {
        $this->header = $header;
    }

    /**
     *
     * Override current element value:
     * - Correct data/header points
     * - Assign values to the corresponding header column
     * @see Vatsimphp\Filter.AbstractFilter::current()
     */
    public function current()
    {
        $values = $this->convertToArray(parent::current(), ':');

        $cntHeader = count($this->header);
        $cntValues = count($values);

        // ignore if more data received then we have headers
        if (empty($this->header) || $cntHeader < $cntValues) {
            return false;
        }

        // correct data
        if ($cntHeader > $cntValues) {
            $values = $this->fixData($values);
        }
        return array_combine($this->header, $values);
    }

    /**
     *
     * Fix data points
     *
     * It seems some data points are empty at the end
     * which got truncated in the data resulting in an
     * offset between header and actual data points.
     *
     * This method corrects the data by adding missing
     * empty values at the end.
     *
     * @param array $data
     * @return array
     */
    protected function fixData($data)
    {
        $addCols = count($this->header) - count($data);
        for ($i = 1; $i <= $addCols; $i++) {
            $data[] = '';
        }
        return $data;
    }
}
