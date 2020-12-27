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

namespace Vatsimphp\Sync;

/**
 * Retrieve data file from VATSIM.
 *
 * @property \Vatsimphp\Parser\DataParser parser
 */
class DataSync extends BaseSync
{
    /**
     * @see Vatsimphp\Parser.DataParser::dataExpire
     *
     * @var int
     */
    public $dataExpire = 3600;

    /**
     * @see Vatsimphp\Sync.SyncInterface::setDefaults()
     */
    public function setDefaults()
    {
        $this->setParser('DataV3Compat');
        $this->cacheFile = 'vatsim-data.json';
        // As per https://forums.vatsim.net/blogs/entry/1-vatsim-tech-blog-q4-2020/
        // Updated more frequently (at present, a minute but we are looking to
        // bring this down to 30, possibly even every 15 seconds!)
        $this->refreshInterval = 90;
    }

    /**
     * Override to support timestamp expiration check
     * if enabled using $this->dataExpire.
     *
     * @see Vatsimphp\Sync.AbstractSync::isDataValid()
     */
    protected function isDataValid($data)
    {
        $this->parser->dataExpire = $this->dataExpire;

        return parent::isDataValid($data);
    }
}
