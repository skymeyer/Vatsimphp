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

use Vatsimphp\Exception\RuntimeException;

/**
 *
 * Logger factory
 *
 */
class LoggerFactory
{
    /**
     *
     * Key for default logger
     * @var unknown_type
     */
    const DEFAULT_LOGGER = '_DEFAULT_';

    /**
     *
     * Logger objects
     * @var array
     */
    protected static $loggers = array();

    /**
     *
     * Log level
     * @var integer
     */
    public static $level = Logger::DEBUG;

    /**
     *
     * Log file name
     * @var string
     */
    public static $file;

    /**
     *
     * Load logger object
     * @param string|object $channel
     * @return \Psr\Log\LoggerInterface
     */
    public static function get($channel)
    {
        // in case an object is passed in we use the base class name as the channel
        if (is_object($channel)) {
            $ref = new \ReflectionClass($channel);
            $channel = $ref->getShortName();
        }

        // custom log channels need to be registered in advance using self::register()
        if (!self::channelExists($channel)) {

            // use default logger object if set or fallback to builtin logger
            if (self::channelExists(self::DEFAULT_LOGGER)) {
                self::$loggers[$channel] = self::$loggers[self::DEFAULT_LOGGER];
            } else {
                $file = empty(self::$file) ? __DIR__ . '/../../../app/logs/vatsimphp.log' : self::$file;
                self::$loggers[$channel] = new Logger($channel, $file, self::$level);
            }
        }
        return self::$loggers[$channel];
    }

    /**
     *
     * Register log object for given channel
     * @param string $channel
     * @param \Psr\Log\AbstractLogger $logger
     */
    public static function register($channel, \Psr\Log\LoggerInterface $logger)
    {
        self::$loggers[$channel] = $logger;
        return $logger;
    }

    /**
     *
     * Deregister log objects (or given channel)
     * @param string $channel
     * @return boolean
     */
    public static function deregister($channel = null)
    {
        if (empty($channel)) {
            self::$loggers = array();
            return true;
        }
        if (isset(self::$loggers[$channel])) {
            unset(self::$loggers[$channel]);
            return true;
        }
        return false;
    }

    /**
     *
     * Verify if channel exists
     * @param string $channel
     * @return boolean
     */
    public static function channelExists($channel)
    {
        if (isset(self::$loggers[$channel])) {
            return true;
        }
        return false;
    }
}
