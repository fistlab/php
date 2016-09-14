<?php

namespace Fist\Database\Query\Grammars;

use Fist\Database\Query\Builder;

class SqliteGrammar extends MysqlGrammar
{
    public function compileTruncateAggregator(Builder $builder)
    {
        $table = $this->wrapTable(
            $builder->getTable()
        );

        return "DELETE FROM {$table}";
    }
}
