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
 * Filter all lines for a given section
 *
 */
class SectionFilter extends AbstractFilter
{
    /**
     *
     * Flag when we have reached the section
     * @var boolean
     */
    protected $inSection = false;

    /**
     *
     * @see Vatsimphp\Filter.AbstractFilter::setFilter()
     */
    public function setFilter($section)
    {
        $this->filter = "!".strtoupper($section).":";
    }

    /**
     *
     * @see Vatsimphp\Filter.FilterInterface::applyFilter()
     */
    public function applyFilter()
    {
        $line = $this->getInnerIterator()->current();

        // mark end of section when hitting next section
        if (substr($line, 0, 1) == '!' && $this->inSection) {
            $this->inSection = false;
        }

        // mark begin of section
        if (substr($line, 0, strlen($this->filter)) == $this->filter) {
            $this->inSection = true;
            return false;
        }

        return $this->inSection;
    }
}
