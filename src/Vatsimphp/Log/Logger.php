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

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as BaseLogger;

/**
 *
 * Simple base logger
 *
 */
class Logger extends BaseLogger
{
    const FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context%\n";

    /**
     *
     * Shared stream handler
     * @var \Monolog\Handler\StreamHandler
     */
    protected static $handler;

    /**
     *
     * Ctor
     * @param string $name
     * @param string $file
     * @param integer $level
     */
    public function __construct($name, $file, $level)
    {
        parent::__construct($name);
        $this->pushHandler($this->getHandler($file, $level));
    }

    /**
     *
     * Get streamhandler
     * @param string $file
     * @param integer $level
     * @return \Monolog\Handler\StreamHandler
     */
    protected function getHandler($file, $level)
    {
        if (empty(self::$handler)) {
            $handler = new StreamHandler($file, $level);
            $handler->setFormatter($this->getCustomFormatter());
            self::$handler = $handler;
        }
        return self::$handler;
    }

    /**
     *
     * Get formatter
     * @return \Monolog\Formatter\LineFormatter
     */
    protected function getCustomFormatter()
    {
        return new LineFormatter(self::FORMAT);
    }

    /**
     *
     * Reset handler
     * @codeCoverageIgnore
     */
    public static function resetHandler()
    {
        self::$handler = null;
    }
}
