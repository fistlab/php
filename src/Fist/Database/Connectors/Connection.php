<?php

namespace Fist\Database\Connectors;

use Fist\Database\Query\Builder as QueryBuilder;
use Fist\Database\Query\Statement;
use Fist\Repository\RepositoryInterface;
use PDOStatement;

abstract class Connection implements ConnectionInterface
{
    protected $repository;

    /**
     * PDO instance.
     *
     * @var \PDO
     */
    protected $pdo;

    public function __construct(RepositoryInterface $repository = null)
    {
        if (!is_null($repository)) {
            $this->configure($repository);
        }
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    public function configure(RepositoryInterface $repository)
    {
        if ($this->repository != $repository) {
            $this->repository = $repository;

            $this->pdo = $this->newPdo($repository);
        }
    }

    public function table($name)
    {
        return $this->callQueryBuilder('table', [$name]);
    }

    protected function callQueryBuilder($method, array $parameters = [])
    {
        return call_user_func_array([$this->newQueryBuilder(), $method], $parameters);
    }

    protected function newQueryBuilder()
    {
        return new QueryBuilder($this, $this->newQueryGrammar());
    }

    public function statement($sql, array $parameters = [])
    {
        return $this->createStatement(
            $this->pdo->prepare($sql),
            $parameters
        )->execute();
    }

    public function raw($sql, array $parameters = [])
    {
        return $this->createStatement(
            $this->pdo->prepare($sql),
            $parameters
        );
    }

    public function getLastInsertedId()
    {
        return $this->pdo->lastInsertId();
    }

    protected function createStatement(PDOStatement $statement, array $parameters = [])
    {
        return new Statement($this->pdo, $statement, $parameters);
    }

    public function getTablePrefix()
    {
        return $this->repository->get('prefix');
    }
}
