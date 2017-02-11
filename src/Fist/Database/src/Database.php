<?php

namespace Fist\Database;

use Closure;
use Fist\Database\Connectors\ConnectionInterface;
use Fist\Repository\ArrayRepository;
use Fist\Repository\RepositoryInterface;
use PDO;

class Database
{
    protected $repository;

    protected $connectedDrivers = [];

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function setRepository(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getDefaultConnection($default = 'default')
    {
        return $this->repository->get('default.connection', $default);
    }

    public function getDefaultDriver($default = 'mysql')
    {
        return $this->repository->get('default.driver', $default);
    }

    /**
     * Get connection configuration repository by name.
     *
     * @param string      $key
     * @param null|string $default
     *
     * @return null|\Fist\Repository\RepositoryInterface
     */
    public function getConnection($key, $default = null)
    {
        $connection = $this->repository->get('connections.'.$key, $default);

        if ($connection instanceof Closure) {
            $connection = $connection();
        }

        if (is_array($connection)) {
            $connection = new ArrayRepository($connection);
        }

        return $connection;
    }

    public function hasConnection($key)
    {
        return $this->repository->has('connections.'.$key);
    }

    public function setConnection($key, $value)
    {
        if (is_string($value)) {
            $value = new $value();
        }

        $this->repository->set('connections.'.$key, $value);
    }

    /**
     * Get driver by name.
     *
     * @param string      $key
     * @param null|string $default
     *
     * @return null|\Fist\Database\Connectors\Connection
     */
    public function getDriver($key, $default = null)
    {
        $driver = $this->repository->get('drivers.'.$key, $default);

        if ($driver instanceof Closure) {
            $driver = $driver();
        }

        if (is_string($driver)) {
            $driver = new $driver();
        }

        return clone $driver;
    }

    public function hasDriver($key)
    {
        return $this->repository->has('drivers.'.$key);
    }

    public function setDriver($key, $value)
    {
        if (is_string($value)) {
            $value = new $value();
        }

        $this->repository->set('drivers.'.$key, $value);
    }

    public function setDrivers(array $drivers)
    {
        foreach ($drivers as $name => $driver) {
            $this->setDriver($name, $driver);
        }
    }

    public function setDefaultConnection($connection)
    {
        $this->setDefaultValue('connection', $connection);
    }

    public function setDefaultDriver($driver)
    {
        $this->setDefaultValue('driver', $driver);
    }

    public function setDefaultValue($key, $value)
    {
        $this->repository->set('default.'.$key, $value);
    }

    public function setDefaultValues(array $values)
    {
        foreach ($values as $name => $value) {
            $this->setDefaultValue($name, $value);
        }
    }

    public function setConnections(array $connections)
    {
        foreach ($connections as $name => $connection) {
            if (is_array($connection)) {
                $connection = new ArrayRepository($connection);
            }

            $this->setConnection($name, $connection);
        }
    }

    public function connection($name = null)
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        $connection = $this->getConnection($name);

        if (is_null($connection)) {
            throw new DatabaseException("Connection [{$name}] does not exists.");
        }

        $driverName = $connection->get('driver', $this->getDefaultDriver());

        if ($this->hasConnectedDriver($driverName, $name)) {
            $driver = $this->getConnectedDriver($driverName, $name);
        } else {
            $driver = $this->getDriver($driverName);
        }

        if (is_null($driver)) {
            throw new DatabaseException("Driver [{$driverName}] does not exists.");
        }

        $this->setConnectedDriver($driverName, $name, $driver);

        $driver->configure($connection);

        return $driver;
    }

    protected function getConnectedDriver($driverName, $connectionName, $default = null)
    {
        if ($this->hasConnectedDriver($driverName, $connectionName)) {
            return $this->connectedDrivers[$driverName][$connectionName];
        }

        return $default;
    }

    protected function hasConnectedDriver($driverName, $connectionName)
    {
        if (isset($this->connectedDrivers[$driverName])) {
            return isset($this->connectedDrivers[$driverName][$connectionName]);
        }

        return false;
    }

    protected function setConnectedDriver($driverName, $connectionName, ConnectionInterface $connection)
    {
        if (!isset($this->connectedDrivers[$driverName])) {
            $this->connectedDrivers[$driverName] = [];
        }

        $this->connectedDrivers[$driverName][$connectionName] = $connection;
    }

    public function table($name)
    {
        return $this->callConnectionDriver('table', [$name]);
    }

    public function createTable($name, $closure)
    {
        return $this->callConnectionDriver('createTable', [$name, $closure]);
    }

    protected function callConnectionDriver($method, array $arguments = [])
    {
        return call_user_func_array([$this->connection(), $method], $arguments);
    }

    public function statement($sql, array $parameters = [])
    {
        return $this->callConnectionDriver('statement', [$sql, $parameters]);
    }

    public function raw($sql, array $parameters = [])
    {
        return $this->callConnectionDriver('raw', [$sql, $parameters]);
    }

    public function getLastInsertedId()
    {
        return $this->callConnectionDriver('getLastInsertedId');
    }
}
