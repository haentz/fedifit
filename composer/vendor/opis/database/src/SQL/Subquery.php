<?php
/* ===========================================================================
 * Copyright 2013-2018 Opis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Database\SQL;

class Subquery
{
    /** @var    SelectStatement */
    protected $select;

    /**
     * @param   string|array $tables
     *
     * @return  SelectStatement
     */
    public function from($tables)
    {
        return $this->select = new SelectStatement($tables);
    }

    /**
     * @return SQLStatement
     */
    public function getSQLStatement(): SQLStatement
    {
        return $this->select->getSQLStatement();
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        $this->select = clone $this->select;
    }
}
