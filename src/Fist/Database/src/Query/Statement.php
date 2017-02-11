<?php

namespace Fist\Database\Query;

use PDO;
use PDOStatement;

class Statement
{
    /**
     * The PDOStatement instance.
     *
     * @var \PDOStatement
     */
    protected $statement;

    /**
     * Binding parameters.
     *
     * @var array
     */
    protected $bindings;

    /**
     * The PDO instance.
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * Statement constructor.
     *
     * @param \PDO          $pdo
     * @param \PDOStatement $statement
     * @param array         $bindings
     */
    public function __construct(PDO $pdo, PDOStatement $statement, array $bindings = [])
    {
        $this->pdo = $pdo;

        $this->statement = $statement;

        $this->bindings = $bindings;
    }

    /**
     * Execute query statement.
     *
     * @return array
     */
    public function execute()
    {
        if ($this->statement->execute($this->bindings)) {
            return $this->statement->fetchAll(PDO::FETCH_OBJ);
        }
    }

    /**
     * Get query string.
     *
     * @return string
     */
    public function toSql()
    {
        return $this->statement->queryString;
    }

    /**
     * Get query string with bindings.
     *
     * @return string
     */
    public function toSqlWithBindings()
    {
        return array_reduce(
            $this->bindings,
            function ($sql, $binding) {
                return preg_replace('/\?/', $this->quote($binding), $sql, 1);
            },
            $this->toSql()
        );
    }

    /**
     * Get PDOStatement instance.
     *
     * @return \PDOStatement
     */
    public function getPdoStatement()
    {
        return $this->statement;
    }

    /**
     * Get binding parameters.
     *
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Quote parameter according to its type.
     *
     * @param $parameter
     *
     * @return string
     */
    protected function quote($parameter)
    {
        if (is_string($parameter)) {
            return $this->pdo->quote($parameter);
        }

        return $parameter;
    }
}
