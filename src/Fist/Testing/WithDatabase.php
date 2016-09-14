<?php

namespace Fist\Testing;

use Closure;
use Fist\Database\Database;

trait WithDatabase
{
    protected function setDatabaseConnectionsAndDrivers(Database $database)
    {
        foreach ($this->testConnections as $connection => $values) {
            $this->setDatabaseConnection($database, $connection, $values);

            $driver = isset($values['driver']) ? $values['driver'] : null;

            $this->setDatabaseDriver($database, $driver);
        }
    }

    protected function setDatabaseConnection(Database $database, $name, $value = null)
    {
        if (is_null($value)) {
            $value = $this->testConnections[$name];
        }

        $database->setConnection($name, $value);
    }

    protected function setDatabaseDriver(Database $database, $name)
    {
        $class = 'Fist\\Database\\Connectors\\'.ucfirst($name).'Connection';

        $database->setDriver($name, $class);
    }

    protected function runOnDatabaseConnections(Database $database, array $connections, Closure $closure)
    {
        foreach ($connections as $connection) {
            $closure($database->connection($connection));
        }
    }
}
