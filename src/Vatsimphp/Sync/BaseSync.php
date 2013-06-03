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

namespace Vatsimphp\Sync;

use \Vatsimphp\Exception\RuntimeException;

/**
 *
 * Base synchronization class for all secondary data
 * implementations. The primary synchronization happens
 * using StatusSync to get the available data urls
 * during the secondary phase.
 *
 */
abstract class BaseSync extends AbstractSync
{
    /**
     *
     * Use StatusSync to dynamically add the available
     * data urls to poll new data from
     * @param \Vatsimphp\Sync\StatusSync $sync
     * @param string $type - the type of urls to use (ie dataUrls)
     * @throws \Vatsimphp\Exception\RuntimeException
     */
    public function registerUrlFromStatus(\Vatsimphp\Sync\StatusSync $sync, $type)
    {
        $urls = $sync->loadData()->get($type)->toArray();
        if (empty($urls)) {
            throw new RuntimeException(
                'Error loading urls from StatusSync'
            );
        }
        $this->registerUrl($urls, true);
        return true;
    }
}
