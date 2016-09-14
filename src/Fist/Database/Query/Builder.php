<?php

namespace Fist\Database\Query;

use Fist\Database\Connectors\Connection;
use Fist\Database\Query\Grammars\GrammarInterface;

class Builder
{
    protected $connection;

    protected $grammar;

    protected $table;

    protected $aggregator = 'select';

    protected $select = [];

    protected $values = [];

    protected $where = [];

    protected $groups = [];

    protected $limit;

    protected $orders = [];

    protected $offset;

    protected $primaryKey = 'id';

    protected $joins = [];

    public function __construct(Connection $connection, GrammarInterface $grammar)
    {
        $this->connection = $connection;

        $this->grammar = $grammar;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function table($name)
    {
        $this->table = $name;

        return $this;
    }

    public function select(array $columns)
    {
        $this->select = $columns;

        return $this;
    }

    public function where($column, $operator, $value = null)
    {
        if (is_null($value)) {
            $value = $operator;

            $operator = '=';
        }

        $this->where[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'aggregator' => 'AND',
        ];

        return $this;
    }

    public function orWhere($column, $operator, $value = null)
    {
        if (is_null($value)) {
            $value = $operator;

            $operator = '=';
        }

        $this->where[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'aggregator' => 'OR',
        ];

        return $this;
    }

    function orderBy($column, $direction = 'ASC')
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => $direction
        ];

        return $this;
    }

    function groupBy($column)
    {
        $this->groups[] = $column;

        return $this;
    }

    public function innerJoin($table, $key, $operator, $foreign = null)
    {
        if (is_null($foreign)) {
            $foreign = $operator;

            $operator = '=';
        }

        $this->joins[] = [
            'type' => 'inner',
            'table' => $table,
            'key' => $key,
            'foreign' => $foreign,
            'operator' => $operator,
        ];

        return $this;
    }

    public function rightJoin($table, $key, $operator, $foreign = null)
    {
        if (is_null($foreign)) {
            $foreign = $operator;

            $operator = '=';
        }

        $this->joins[] = [
            'type' => 'right',
            'table' => $table,
            'key' => $key,
            'foreign' => $foreign,
            'operator' => $operator,
        ];

        return $this;
    }

    public function leftJoin($table, $key, $operator, $foreign = null)
    {
        if (is_null($foreign)) {
            $foreign = $operator;

            $operator = '=';
        }

        $this->joins[] = [
            'type' => 'left',
            'table' => $table,
            'key' => $key,
            'foreign' => $foreign,
            'operator' => $operator,
        ];

        return $this;
    }

    public function outerJoin($table, $key, $operator, $foreign = null)
    {
        if (is_null($foreign)) {
            $foreign = $operator;

            $operator = '=';
        }

        $this->joins[] = [
            'type' => 'outer',
            'table' => $table,
            'key' => $key,
            'foreign' => $foreign,
            'operator' => $operator,
        ];

        return $this;
    }

    public function truncate()
    {
        $this->aggregator = 'truncate';

        return $this->connection->statement(
            $this->toSql()
        );
    }

    public function get()
    {
        $this->aggregator = 'select';

        return $this->connection->statement(
            $this->toSql()
        );
    }

    public function update(array $values)
    {
        $this->aggregator = 'update';

        $this->values = $values;

        return $this->connection->statement(
            $this->toSql()
        );
    }

    public function insert(array $values)
    {
        $this->aggregator = 'insert';

        $this->values = $values;

        return $this->connection->statement(
            $this->toSql()
        );
    }

    public function delete()
    {
        $this->aggregator = 'delete';

        return $this->connection->statement(
            $this->toSql()
        );
    }

    public function first()
    {
        $this->aggregator = 'select';

        $this->limit = 1;

        $results = $this->connection->statement(
            $this->toSql()
        );

        return isset($results[0]) ? $results[0] : null;
    }

    public function last()
    {
        $this->aggregator = 'select';

        $this->orderBy(
            $this->getPrimaryKey(),
            'DESC'
        );
        $this->limit = 1;

        $results = $this->connection->statement(
            $this->toSql()
        );

        return isset($results[0]) ? $results[0] : null;
    }

    public function toSql()
    {
        return $this->grammar->toSql($this);
    }

    public function getAggregator()
    {
        return $this->aggregator;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getTablePrefix()
    {
        return $this->connection->getTablePrefix();
    }

    public function getSelect()
    {
        return $this->select;
    }

    public function getWhereStatements()
    {
        return $this->where;
    }

    public function getOrders()
    {
        return $this->orders;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function getJoins()
    {
        return $this->joins;
    }
}
