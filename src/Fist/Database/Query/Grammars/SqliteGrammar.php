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

    protected function compileOrdersComponent(Builder $builder)
    {
        $orders = $builder->getOrders();

        if (empty($orders)) {
            return;
        }

        return 'ORDER BY '.implode(', ', array_map(function ($order) {
            if ($order['random']) {
                return 'RANDOM()';
            }

            $column = $this->wrapColumn($order['column']);

            $direction = isset($order['direction']) ? $order['direction'] : null;

            if (is_null($direction)) {
                return $column;
            }

            return "{$column} {$direction}";
        }, $orders));
    }
}
