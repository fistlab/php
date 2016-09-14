<?php

namespace Fist\Database\Query\Grammars;

use Fist\Database\Query\Builder;

class MysqlGrammar extends Grammar
{
    protected $components = ['columns', 'table', 'joins', 'values', 'wheres', 'groups', 'orders', 'limit', 'offset'];

    protected function getSelectAggregatorPrefix()
    {
        return 'SELECT';
    }

    protected function getUpdateAggregatorPrefix()
    {
        return 'UPDATE';
    }

    protected function getInsertAggregatorPrefix()
    {
        return 'INSERT INTO';
    }

    protected function getDeleteAggregatorPrefix()
    {
        return 'DELETE FROM';
    }

    protected function compileColumnsComponent(Builder $builder, $aggregator)
    {
        if (in_array($aggregator, ['insert', 'update', 'delete', 'truncate'])) {
            return;
        }

        return $this->columnize(
            $builder->getSelect()
        );
    }

    protected function compileValuesComponent(Builder $builder, $aggregator)
    {
        if (in_array($aggregator, ['select', 'insert', 'delete', 'truncate'])) {
            return;
        }

        $values = $builder->getValues();

        return 'SET '.implode(', ', array_map(function ($column, $value) {
            $column = $this->wrapColumn($column);
            $value = $this->wrapValue($value);

            return "{$column} = {$value}";
        }, array_keys($values), $values));
    }

    protected function compileValuesComponentForInsertAggregator(Builder $builder)
    {
        $values = $builder->getValues();
        $keys = isset($values[0]) ? array_keys($values[0]) : [];

        return '('.implode(', ', array_map(function ($column) {
            return $this->wrapColumn($column);
        }, $keys)).') VALUES '.implode(', ', array_map(function ($items) {
            return '('.implode(', ', array_map(function ($item) {
                return $this->wrapValue($item);
            }, $items)).')';
        }, $builder->getValues()));
    }

    protected function compileTableComponent(Builder $builder, $aggregator)
    {
        $table = $this->wrapTable(
            $builder->getTable()
        );

        switch ($aggregator) {
            case 'select': return "FROM {$table}";
            default: return $table;
        }
    }

    protected function compileWheresComponent(Builder $builder)
    {
        $wheres = [];

        foreach ($builder->getWhereStatements() as $index => $where) {
            $column = $this->wrapColumn($where['column']);
            $operator = $where['operator'];
            $value = $this->wrapValue($where['value']);
            $aggregator = $index > 0 ? $where['aggregator'] : 'WHERE';

            $wheres[] = "{$aggregator} {$column} {$operator} {$value}";
        }

        return implode(' ', $wheres);
    }

    protected function compileOrdersComponent(Builder $builder)
    {
        $orders = $builder->getOrders();

        if (empty($orders)) {
            return;
        }

        return 'ORDER BY '.implode(', ', array_map(function ($order) {
            if ($order['random']) {
                return 'RAND()';
            }

            $column = $this->wrapColumn($order['column']);

            $direction = isset($order['direction']) ? $order['direction'] : null;

            if (is_null($direction)) {
                return $column;
            }

            return "{$column} {$direction}";
        }, $orders));
    }

    protected function compileLimitComponent(Builder $builder)
    {
        $limit = $builder->getLimit();

        if (is_null($limit)) {
            return;
        }

        return "LIMIT {$limit}";
    }

    protected function compileOffsetComponent(Builder $builder)
    {
        $offset = $builder->getOffset();

        if (is_null($offset)) {
            return;
        }

        return [", {$offset}", false];
    }

    protected function compileGroupsComponent(Builder $builder)
    {
        $groups = $builder->getGroups();

        if (empty($groups)) {
            return;
        }

        return 'GROUP BY '.implode(', ', array_map(function ($group) {
            return $this->wrapColumn($group);
        }, $groups));
    }

    protected function compileJoinsComponent(Builder $builder)
    {
        $joins = $builder->getJoins();

        if (empty($joins)) {
            return;
        }

        return implode(' ', array_map(function ($join) {
            $type = $join['type'];
            $table = $this->wrapTable($join['table']);
            $key = $this->wrapColumn($join['key']);
            $operator = $join['operator'];
            $foreign = $this->wrapColumn($join['foreign']);

            return "{$type} JOIN {$table} ON {$key} {$operator} {$foreign}";
        }, $joins));
    }

    public function getComponents()
    {
        return $this->components;
    }
}
