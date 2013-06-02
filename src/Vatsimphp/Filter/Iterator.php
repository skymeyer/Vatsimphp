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
 * Iterator without applying filtering
 *
 */
class Iterator extends AbstractFilter
{
    /**
     *
     * @see Vatsimphp\Filter.AbstractFilter::__construct()
     */
    public function __construct($iterator)
    {
        parent::__construct($iterator);
        $this->skipComments = false;
    }

    /**
     *
     * @see Vatsimphp\Filter.FilterInterface::applyFilter()
     */
    public function applyFilter()
    {
        return true;
    }
}
