<?php

namespace Fist\Database\Connectors;

use PDO;
use Fist\Repository\RepositoryInterface;
use Fist\Database\Query\Grammars\SqliteGrammar;

class SqliteConnection extends Connection
{
    public function newPdo(RepositoryInterface $repository)
    {
        $database = $repository->get('database');

        $pdo = new PDO("sqlite:{$database}");

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public function newQueryGrammar()
    {
        return new SqliteGrammar();
    }
}
