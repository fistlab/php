<?php

namespace Fist\Database\Connectors;

use Fist\Database\Query\Grammars\MysqlGrammar;
use PDO;
use Fist\Repository\RepositoryInterface;

class MysqlConnection extends Connection
{
    public function newPdo(RepositoryInterface $repository)
    {
        $hostname = $repository->get('hostname', 'localhost');
        $database = $repository->get('database', '');

        $pdo = new PDO(
            "mysql:host={$hostname};dbname={$database}",
            $repository->get('username'),
            $repository->get('password')
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public function newQueryGrammar()
    {
        return new MysqlGrammar();
    }
}
