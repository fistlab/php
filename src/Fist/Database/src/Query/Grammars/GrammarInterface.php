<?php

namespace Fist\Database\Query\Grammars;

use Fist\Database\Query\Builder;

interface GrammarInterface
{
    public function toSql(Builder $builder);

    public function getAggregatorComponents($aggregator);

    public function columnize(array $columns);

    public function wrap($value, $prefixAlias = false);

    public function wrapTable($table);

    public function wrapColumn($column);

    public function wrapValue($value);

    public function getComponents();
}
