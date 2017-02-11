<?php

namespace Fist\Database\Query\Grammars;

use Fist\Database\Query\Builder;
use Fist\Database\DatabaseException;

abstract class Grammar implements GrammarInterface
{
    protected $tablePrefix;

    protected $aggregators = ['select', 'update', 'insert', 'delete', 'truncate'];

    public function toSql(Builder $builder)
    {
        $this->tablePrefix = $builder->getTablePrefix();

        return $this->compileAggregator(
            $builder,
            $builder->getAggregator()
        );
    }

    public function compileAggregator(Builder $builder, $aggregator)
    {
        if (is_null($aggregator)) {
            throw new DatabaseException('Missing aggregator.');
        }

        if (! in_array($aggregator, $this->aggregators)) {
            throw new DatabaseException("Invalid aggregator [{$aggregator}].");
        }

        $method = 'compile'.ucfirst($aggregator).'Aggregator';

        if (method_exists($this, $method)) {
            return $this->$method($builder, $aggregator);
        }

        $method = 'get'.ucfirst($aggregator).'AggregatorPrefix';

        $prefix = method_exists($this, $method) ? $this->$method($builder, $aggregator) : null;

        $method = 'get'.ucfirst($aggregator).'AggregatorSuffix';

        $suffix = method_exists($this, $method) ? $this->$method($builder, $aggregator) : null;

        $string = '';

        foreach ($this->getAggregatorComponents($aggregator) as $component) {
            $method = 'compile'.ucfirst($component).'ComponentFor'.ucfirst($aggregator).'Aggregator';

            if (! method_exists($this, $method)) {
                $method = 'compile'.ucfirst($component).'Component';
            }

            if ($content = $this->$method($builder, $aggregator)) {
                if (! is_array($content)) {
                    $content = [$content, true];
                }

                $space = isset($content[1]) ? $content[1] : true;
                $string .= $space ? ' ' : '';
                $string .= $content[0];
            }
        }

        return "{$prefix}{$string}{$suffix}";
    }

    public function getAggregatorComponents($aggregator)
    {
        $method = 'get'.ucfirst($aggregator).'Components';

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return $this->getComponents();
    }

    /**
     * Convert an array of column names into a delimited string.
     *
     * @param array $columns
     *
     * @return string
     */
    public function columnize(array $columns)
    {
        if (empty($columns)) {
            return '*';
        }

        return implode(', ', array_map([$this, 'wrap'], $columns));
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param string $value
     * @param bool   $prefixAlias
     *
     * @return string
     */
    public function wrap($value, $prefixAlias = false)
    {
        // Work for "as" aliases.
        if (is_array($value) && count($value) === 2) {
            if ($prefixAlias) {
                $value[1] = $this->tablePrefix.$value[1];
            }

            return $this->wrap($value[0]).' as '.$this->wrap($value[1]);
        }

        $wrapped = [];

        $segments = explode('.', $value);

        // Wrap first segment as table, and the rest (if any) as regular values
        foreach ($segments as $key => $segment) {
            if ($key == 0 && count($segments) > 1) {
                $wrapped[] = $this->wrapTable($segment);
            } else {
                $wrapped[] = $this->wrapColumn($segment);
            }
        }

        return implode('.', $wrapped);
    }

    public function wrapTable($table)
    {
        $prefix = $this->tablePrefix;

        return "`{$prefix}{$table}`";
    }

    public function wrapColumn($column)
    {
        return "`{$column}`";
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param string $value
     *
     * @return string
     */
    public function wrapValue($value)
    {
        if ($value === '*') {
            return $value;
        }

        return '"'.str_replace('"', '""', $value).'"';
    }
}
