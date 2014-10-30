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
 * Abstract filter class based on SPL FilterIterator
 *
 */
abstract class AbstractFilter extends \FilterIterator implements FilterInterface, \Countable
{
    /**
     *
     * Filter to be applied
     * @var string
     */
    protected $filter = '';

    /**
     *
     * Filter comment or empty lines
     * @var boolean
     */
    protected $skipComments = true;

    /**
     *
     * Negative filtering
     * @var boolean
     */
    protected $negate = false;

    /**
     *
     * Ctor
     * @param array|\Iterator $iterator
     */
    public function __construct($iterator)
    {
        if (!$iterator instanceof \Iterator) {
            $iterator = new \ArrayIterator($iterator);
        }
        parent::__construct($iterator);
    }

    /**
     *
     * Set filter
     * @param string $filter
     */
    public function setFilter($filter)
    {
        if (is_string($filter)) {
            $this->filter = $filter;
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * Return array for this iterator
     * @param boolean $retainKeys
     * @return array
     */
    public function toArray($retainKeys = true)
    {
        return iterator_to_array($this, $retainKeys);
    }

    /**
     *
     * @see    \Countable
     * @return integer
     */
    public function count()
    {
        return count($this->toArray());
    }

    /**
     *
     * @see FilterIterator::accept()
     * @return boolean
     */
    final public function accept()
    {
        // skip comments and empty lines
        $line = $this->getInnerIterator()->current();
        if ($this->skipComments && $this->isComment($line)) {
            return false;
        }
        return $this->negate xor $this->applyFilter();
    }

    /**
     *
     * @see FilterIterator::current()
     */
    public function current()
    {
        $value = parent::current();
        if (is_string($value)) {
            $value = trim($value);
        }
        return $value;
    }

    /**
     *
     * Check if current element is a comment
     * @params mixed $line
     * @return boolean
     */
    protected function isComment($line)
    {
        if (is_string($line) && (substr($line, 0, 1) == ';' || trim($line) == '')) {
            return true;
        }
        return false;
    }

    /**
     *
     * Helper function to convert string to array
     * @param string $data
     * @param string $sep
     * @return array
     */
    protected function convertToArray($data, $sep)
    {
        return explode($sep, rtrim($data, $sep));
    }
}
