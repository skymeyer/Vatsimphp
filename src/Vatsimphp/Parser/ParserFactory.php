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

namespace Vatsimphp\Parser;

use Vatsimphp\Exception\RuntimeException;

/**
 * Factory class for Parsers.
 */
class ParserFactory
{
    /**
     * Return new parser object.
     *
     * @param string $name
     *
     * @throws RuntimeException
     *
     * @return \Vatsimphp\Parser\AbstractParser
     */
    public static function getParser($name)
    {
        $className = "\Vatsimphp\Parser\\".$name.'Parser';
        if (!class_exists($className, true)) {
            throw new RuntimeException("Unable to load parser '{$name}'");
        }

        return new $className();
    }
}
