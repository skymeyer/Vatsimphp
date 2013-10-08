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

namespace Vatsimphp\Log;

use Psr\Log\AbstractLogger;

/**
 *
 * Simple base logger
 *
 */
class Logger extends AbstractLogger
{
    /**
     *
     * Log prefix
     * @var string
     */
    protected $prefix;

    /**
     *
     * Ctor
     * @param string $prefix
     */
    public function __construct($prefix = '')
    {
        $this->prefix = $prefix;
    }

    /**
     *
     * Return prefix (channel name)
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Simple logger implementation
     * @see Psr\Log.LoggerInterface::log()
     * @return string
     */
    public function log($level, $message, Array $context = array())
    {
        if (!empty($this->prefix)) {
            $message = "$this->prefix: $message";
        }
        $message = "[".strtoupper($level)."] $message";

        if ($context = $this->getContext($context)) {
            $message = $message . PHP_EOL . $context;
        }
        $this->logStdErr($message);
        return $message;
    }

    /**
     *
     * Convert array to key/value string
     * @param array $context
     * @return string
     */
    protected function getContext($context)
    {
        $result = array();
        foreach ($context as $key => $value) {
            if (is_string($value)) {
                $result[] = "$key: $value";
            }
        }
        return implode(PHP_EOL, $result);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function logStdErr($message)
    {
        error_log($message);
    }
}
