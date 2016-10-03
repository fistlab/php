# Fistlab Database

The Fistlab Database component is a database toolkit for PHP, providing an expressive query builder. It currently supports MySQL and SQLite.

## Installation

Install using Composer.
```
composer require fist/database
```

Then load it using Composer's autoload.

## Requirements

* [Fist/Repository](https://github.com/fistphp/repository)

## Usage

The [`$repository`](https://github.com/fistphp/repository) must be an instance of [`Fist\Repository\RepositoryInterface`](https://github.com/fistphp/repository/blob/master/RepositoryInterface.php).    

```
use Fist\Database\Database;

$db = new Database($repository);
```

### Repository

It takes the following values from it:

| Option             | Type                  | Default     | Description                                                     |
|--------------------|-----------------------|-------------|-----------------------------------------------------------------|
| connections.NAME   | `array` or `closure`  |             | The connection options. `NAME` is the requested connection.     |
| default.connection | `string` or `closure` | `"default"` | The default connection to use.                                  |
| default.driver     | `string` or `closure` | `"mysql"`   | The default database driver to use.                             |
| drivers.NAME       | `object` or `closure` |             | The driver to run. `NAME` is the requested connection's driver. |

Here is an example of the `$repository` using [`ArrayRepository`](https://github.com/fistphp/repository/blob/master/ArrayRepository.php).

```
use Fist\Repository\ArrayRepository;

$repository = new ArrayRepository([
    'default' => [
        'connection' => 'default',
        'driver' => 'mysql',
    ],
    'connections' => [
        'default' => [
            'driver' => 'mysql',
            'hostname' => '127.0.0.1',
            'database' => 'database',
            'username' => 'root',
            'password' => '',
        ],
    ],
    'drivers' => [
        'mysql' => \Fist\Database\Connectors\MysqlConnection::class,
    ],
]);
```

Here is an example of the above using [`ContainerRepository`](https://github.com/fistphp/repository/blob/master/ContainerRepository.php).

> This requires the [Container](https://github.com/fistphp/container) component.

```
use Fist\Container\Container;
use Fist\Repository\ContainerRepository;

$container = new Container();

$container->instance('default.driver', 'mysql');
$container->instance('default.connection', 'default');
$container->instance('connections.default', [
    'driver' => 'mysql',
    'hostname' => '127.0.0.1',
    'database' => 'database',
    'username' => 'root',
    'password' => '',
]);
$connection->bind('drivers.mysql', \Fist\Database\Connectors\MysqlConnection::class);

$repository = new ContainerRepository($container);
```

### Methods

##### Statement

Run a SQL statement.

Example: `$db->statement("SELECT * FROM users");`    
Query: `SELECT * FROM users`

Example: `$db->statement("SELECT * FROM users WHERE name = ?", ['mark']);`    
Query: `SELECT * FROM users WHERE name = 'mark'`

##### Table

Run the query builder on a table.

Example: `$db->table('users')->get();`    
Query: `SELECT * FROM users`

##### Where

Run the query builder on a table with where clauses.

Example: `$db->table('users')->where('name', 'mark')->get();`    
Query: `SELECT * FROM users WHERE name = 'mark'`

Example: `$db->table('users')->where('name', '!=', 'mark')->get();`    
Query: `SELECT * FROM users WHERE name != 'mark'`

Example: `$db->table('users')->where('name', 'mark')->where('age', 25)->get();`    
Query: `SELECT * FROM users WHERE name = 'mark' AND age = 25`

Example: `$db->table('users')->where('name', 'mark')->orWhere('age', 25)->get();`    
Query: `SELECT * FROM users WHERE name = 'mark' OR age = 25`
