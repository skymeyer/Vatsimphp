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

use Vatsimphp\Parser\ParserFactory;
use Vatsimphp\Exception\RuntimeException;
use Vatsimphp\Exception\UnexpectedValueException;
use Vatsimphp\Exception\SyncException;
use Vatsimphp\Log\LoggerFactory;

/**
 *
 * Synchronisation base class supporting local cache and
 * multiple url sources. Using the cache and refresh timers
 * external calls are only issued when needed.
 *
 */
abstract class AbstractSync
{
    /**
     *
     * Cache directory
     * @var string
     */
    public $cacheDir = '.';

    /**
     *
     * Cache file
     * @var string
     */
    public $cacheFile = '';

    /**
     *
     * When set ignore local cache file
     * even if it's not expired
     * @var boolean
     */
    public $forceRefresh = false;

    /**
     *
     * Expire interval in seconds for local cache
     * @var integer
     */
    public $refreshInterval = 60;

    /**
     *
     * If set only local file cache will be used and
     * download urls are ignored
     * TODO: implement behavior
     * @var boolean
     */
    public $cacheOnly = false;

    /**
     *
     * List of urls
     * @var array
     */
    protected $urls = array();

    /**
     *
     * Cache file full path
     * @var string
     */
    protected $filePath;

    /**
     *
     * Parser object
     * @var \Vatsimphp\Parser\AbstractParser
     */
    protected $parser;

    /**
     *
     * Sync error array returned on SyncException
     * @var array
     */
    protected $errors = array();

    /**
     *
     * Curl resource
     * @var curl
     */
    protected $curl;

    /**
     *
     * Curl options
     * @var array
     */
    protected $curlOpts = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_SSL_VERIFYPEER => false,
    );

    /**
     *
     * Logger
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

    /**
     *
     * Ctor
     */
    public function __construct()
    {
        $this->log = LoggerFactory::get($this);
    }

    /**
     *
     * Set parser
     * @param string $parserName
     * @param array  $params
     */
    public function setParser($parserName)
    {
        $this->parser = ParserFactory::getParser($parserName);
    }

    /**
     *
     * Add url(s)
     * @param mixed (string|array) $url
     * @param boolean $flush
     */
    public function registerUrl($url, $flush = false)
    {
        if ($flush) {
            $this->urls = array();
        }

        if (is_array($url)) {
            $this->log->debug("Registered urls", $url);
            $this->urls = array_merge($this->urls, $url);
        } else {
            $this->log->debug("Registered url -> $url");
            $this->urls[] = $url;
        }

    }

    /**
     *
     * Return parsed data
     * @throws SyncException
     * @return \Iterator
     */
    public function loadData()
    {
        $this->filePath = "{$this->cacheDir}/{$this->cacheFile}";
        $this->validateConfig();

        // randomize urls
        shuffle($this->urls);

        // if local cache exists, shift it on top
        if (file_exists($this->filePath) && !$this->forceRefresh) {
            array_unshift($this->urls, $this->filePath);
        }

        // we need at least one location
        if (!count($this->urls)) {
            throw new SyncException(
                "No location(s) available to sync from",
                $this->errors
            );
        }

        // cycle urls until valid data is found
        while (count($this->urls) && empty($validData)) {
            $nextUrl = array_shift($this->urls);
            if ($this->getData($nextUrl)) {
                $validData = true;
            }
        }

        // we should have valid data at this point
        if (! $this->parser->isValid()) {
            throw new SyncException(
                "Unable to download data or data invalid",
                $this->errors
            );
        }

        return $this->parser->getParsedData();
    }

    /**
     *
     * Validate config wrapper
     */
    protected function validateConfig()
    {
        $this->validateUrls();
        $this->validateRefreshInterval();
        $this->validateCacheFile();
        $this->validateFilePath();
        $this->validateParser();
        return true;
    }

    /**
     *
     * Validate urls
     * @throws UnexpectedValueException
     */
    protected function validateUrls()
    {
        if (!is_array($this->urls)) {
            throw new UnexpectedValueException(
                "Invalid url format, expecting array"
            );
        }
    }

    /**
     *
     * Validate refreshInterval
     * @throws UnexpectedValueException
     */
    protected function validateRefreshInterval()
    {
        if (!is_int($this->refreshInterval)) {
            throw new UnexpectedValueException(
                "Invalid refresh interval, expecting integer"
            );
        }
    }

    /**
     *
     * Validate cacheFile
     * @throws UnexpectedValueException
     */
    protected function validateCacheFile()
    {
        if (empty($this->cacheFile)) {
            throw new UnexpectedValueException(
                "Cache file name not specified"
            );
        }
    }

    /**
     *
     * Validate filePath
     * @throws RuntimeException
     */
    protected function validateFilePath()
    {
        if (file_exists($this->filePath)) {
            if (!is_writable($this->filePath)) {
                throw new RuntimeException(
                    "File '{$this->filePath}' exist but is not writable"
                );
            }
        } else {
            if (!$fp = @fopen($this->filePath, 'w')) {
                throw new RuntimeException(
                    "File '{$this->filePath}' is not writable"
                );
            }
            fclose($fp);
            unlink($this->filePath);
        }
    }

    /**
     *
     * Validate parser
     * @throws RuntimeException
     */
    protected function validateParser()
    {
        if (!$this->parser instanceof \Vatsimphp\Parser\AbstractParser) {
            throw new RuntimeException(
                "No valid parser object set"
            );
        }
    }

    /**
     *
     * Initialize curl resource
     */
    protected function initCurl()
    {
        if (empty($this->curl)) {
            $this->curl = curl_init();
            curl_setopt_array($this->curl, $this->curlOpts);
            $this->log->debug("cURL object initialized", $this->curlOpts);
        }
    }

    /**
     *
     * Wrapper to load data from url or file
     * @param string $location
     * @return boolean
     */
    protected function getData($location)
    {
        if (stripos($location, 'http') === 0) {
            return $this->loadFromUrl($location);
        } else {
            return $this->loadFromCache();
        }
    }

    /**
     *
     * Load data from url and pass it to the parser
     * If successful save raw data to cache
     * @param string $url
     * @return boolean
     */
    protected function loadFromUrl($url)
    {
        $this->log->debug("Load from url $url");
        $this->initCurl();
        curl_setopt($this->curl, CURLOPT_URL, $url);
        $data = curl_exec($this->curl);

        // check for curl errors
        if (curl_errno($this->curl)) {
            $this->errors[] = array(
                'url' => $url,
                'msg' => curl_error($this->curl),
            );
            return false;
        }

        // validate data through parser
        if (!$this->isDataValid($data)) {
            $this->errors[] = array(
                'url' => $url,
                'msg' => 'Data not valid according to parser',
            );
            return false;
        }

        // save result to disk
        $this->saveToCache($data);
        return true;
    }

    /**
     *
     * Load data from file and pass it to the parser
     * Fails if content is expired
     * @param string $file
     * @return boolean
     */
    protected function loadFromCache()
    {
        $this->log->debug("Load from cache file {$this->filePath}");
        $data = file_get_contents($this->filePath);
        if ($data === false) {
            $data = array();
        }

        // validate data through parser
        if (!$this->isDataValid($data)) {
            $this->errors[] = array(
                'url' => $this->filePath,
                'msg' => 'Data not valid according to parser',
            );
            return false;
        }

        // verify if local cache is expired
        if ($this->isCacheExpired()) {
            $this->errors[] = array(
                'url' => $this->filePath,
                'msg' => 'Local cache is expired',
            );
            return false;
        }
        return true;
    }

    /**
     *
     * Validate the raw data using parser object
     * @param string $data
     * @return boolean
     */
    protected function isDataValid($data)
    {
        $this->parser->setData($data);
        $this->parser->parseData();
        return $this->parser->isValid();
    }

    /**
     *
     * Atomic save raw data to cache file
     * @param string $data
     */
    protected function saveToCache($data)
    {
        $fh = fopen($this->filePath, 'w');
        if (flock($fh, LOCK_EX)) {
            fwrite($fh, $data);
            flock($fh, LOCK_UN);
        }
        fclose($fh);
        $this->log->debug("Cache file {$this->filePath} saved");
    }

    /**
     *
     * Verify if file content is outdated based on
     * the file last modification timestamp
     * @return boolean
     */
    protected function isCacheExpired()
    {
        $ts = filemtime($this->filePath);
        if (time() - $ts > $this->refreshInterval) {
            $this->log->debug("Cache content {$this->filePath} expired ({$this->refreshInterval})");
            return true;
        }
        return false;
    }
}
