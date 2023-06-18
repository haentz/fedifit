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

class DeleteStatement extends BaseStatement
{

    /**
     * DeleteStatement constructor.
     * @param string|array $from
     * @param SQLStatement|null $statement
     */
    public function __construct($from, SQLStatement $statement = null)
    {
        parent::__construct($statement);

        if (!is_array($from)) {
            $from = [$from];
        }

        $this->sql->setFrom($from);
    }

    /**
     * Delete records
     *
     * @param   string|array $tables
     */
    public function delete($tables = [])
    {
        if (!is_array($tables)) {
            $tables = [$tables];
        }
        $this->sql->addTables($tables);
    }
}
