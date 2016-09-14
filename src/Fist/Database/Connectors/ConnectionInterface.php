<?php

namespace Fist\Database\Connectors;

use Fist\Repository\RepositoryInterface;

interface ConnectionInterface
{
    /**
     * Create PDO instance.
     *
     * @param \Fist\Repository\RepositoryInterface $repository
     *
     * @return \PDO
     */
    public function newPdo(RepositoryInterface $repository);

    /**
     * Create Query Grammar instance.
     *
     * @return \Fist\Database\Query\Grammars\GrammarInterface
     */
    public function newQueryGrammar();

    /**
     * Get PDO instance.
     *
     * @return \PDO
     */
    public function getPdo();

    public function statement($sql, array $parameters = []);
}
