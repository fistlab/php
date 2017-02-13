<?php

use Fist\Testing\TestCase;
use Fist\Database\Database;
use Fist\Container\Container;
use Fist\Testing\WithDatabase;
use Fist\Database\Query\Statement;
use Fist\Database\DatabaseException;
use Fist\Repository\ArrayRepository;
use Fist\Repository\ContainerRepository;
use Fist\Repository\RepositoryInterface;
use Fist\Database\Connectors\MysqlConnection;
use Fist\Database\Connectors\SqliteConnection;
use Fist\Database\Connectors\ConnectionInterface;

class DatabaseConnectionTest extends TestCase
{
    use WithDatabase;

    protected $testConnections = [
        'mysql' => [
            'driver' => 'mysql',
            'database' => 'database',
            'hostname' => 'localhost',
            'username' => '',
            'password' => '',
        ],
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => ':memory:', // in-memory
            'hostname' => 'localhost',
            'username' => '',
            'password' => '',
        ],
        'tmp' => [
            'driver' => 'sqlite',
            'database' => '', // temporary
            'hostname' => 'localhost',
            'username' => '',
            'password' => '',
        ],
    ];

    public function testConnectionBuildingUsingArrayRepository()
    {
        $db = new Database(
            new ArrayRepository()
        );

        $this->assertInstanceOf(RepositoryInterface::class, $db->getRepository());
        $this->assertInstanceOf(ArrayRepository::class, $db->getRepository());
    }

    public function testConnectionBuildingUsingContainerRepository()
    {
        $db = new Database(
            new ContainerRepository(
                new Container()
            )
        );

        $this->assertInstanceOf(RepositoryInterface::class, $db->getRepository());
        $this->assertInstanceOf(ContainerRepository::class, $db->getRepository());
    }

    public function testDefaultConnection()
    {
        $db = new Database(
            new ArrayRepository()
        );

        $this->setDatabaseConnectionsAndDrivers($db);

        $this->assertEquals('default', $db->getDefaultConnection());
        $this->throwsException(function () use ($db) {
            $this->assertInstanceOf(MysqlConnection::class, $db->connection());
        }, DatabaseException::class, 'Connection [default] does not exists.');

        $db->setDefaultConnection('mysql');

        $this->assertEquals('mysql', $db->getDefaultConnection());
        $this->throwsException(function () use ($db) {
            $this->assertInstanceOf(MysqlConnection::class, $db->connection());
        }, PDOException::class, [
            "SQLSTATE[HY000] [1045] Access denied for user ''@'localhost' (using password: NO)",
            "SQLSTATE[HY000] [1044] Access denied for user ''@'localhost' to database 'database'",
        ]);

        $db->setDefaultConnection('sqlite');

        $this->assertEquals('sqlite', $db->getDefaultConnection());
        $this->assertInstanceOf(SqliteConnection::class, $db->connection());
    }

    public function testDefaultDriver()
    {
        $db = new Database(
            new ArrayRepository()
        );

        $this->setDatabaseConnectionsAndDrivers($db);
        $db->setConnection('default', []);

        $this->assertEquals('mysql', $db->getDefaultDriver());
        $this->throwsException(function () use ($db) {
            $db->connection()->statement('SELECT * FROM test');
        }, PDOException::class, [
            'SQLSTATE[3D000]: Invalid catalog name: 1046 No database selected',
            "SQLSTATE[HY000] [1045] Access denied for user ''@'localhost' (using password: NO)",
        ]);

        $db->setDefaultDriver('sqlite');
        $this->assertEquals('sqlite', $db->getDefaultDriver());
        $this->assertInstanceOf(SqliteConnection::class, $db->connection());
    }

    public function testConnectionSwapping()
    {
        $db = new Database(
            new ArrayRepository()
        );

        $this->setDatabaseConnectionsAndDrivers($db);
        $db->setDefaultConnection('mysql');

        $this->throwsException(function () use ($db) {
            $this->assertInstanceOf(MysqlConnection::class, $db->connection());
        }, PDOException::class, [
            "SQLSTATE[HY000] [1045] Access denied for user ''@'localhost' (using password: NO)",
            "SQLSTATE[HY000] [1044] Access denied for user ''@'localhost' to database 'database'",
        ]);

        $this->assertInstanceOf(SqliteConnection::class, $db->connection('sqlite'));
    }

    public function testDriverConnectionAreShared()
    {
        $db = new Database(
            new ContainerRepository(
                new Container(), 'db.'
            )
        );

        $this->setDatabaseConnectionsAndDrivers($db);
        $db->setDefaultConnection('sqlite');

        $this->runOnDatabaseConnections($db, ['sqlite', 'tmp'], function (ConnectionInterface $connection) {
            $connection->statement('CREATE TABLE items (name VARCHAR(50))');
        });

        $this->runOnDatabaseConnections($db, [
            'sqlite',
            'tmp',
            'sqlite',
            'tmp',
            null,
        ], function (ConnectionInterface $connection) {
            $this->throwsException(function () use ($connection) {
                $connection->statement('CREATE TABLE items (name VARCHAR(50))');
            }, PDOException::class, 'SQLSTATE[HY000]: General error: 1 table items already exists');
        });
    }

    public function testDriverSwapping()
    {
        $db = new Database(
            $repository = new ContainerRepository(
                new Container(), 'db.'
            )
        );

        $this->setDatabaseConnectionsAndDrivers($db);
        $db->setDefaultConnection('sqlite');

        // Test getting default connection
        $connection = $db->connection();
        $this->assertInstanceOf(SqliteConnection::class, $connection);

        // Change from in-memory to temporary database
        $db->setConnection('sqlite', [
            'driver' => 'mysql',
        ]);

        // Connection fails using the mysql setup since it's not setup.
        // So we expect it to fail
        $this->throwsException(function () use ($db) {
            $db->connection()->statement('SELECT * FROM test');
        }, PDOException::class, [
            'SQLSTATE[3D000]: Invalid catalog name: 1046 No database selected',
            "SQLSTATE[HY000] [1045] Access denied for user ''@'localhost' (using password: NO)",
        ]);
    }

    public function testSharedDriverConnectionsUpdateSettings()
    {
        $db = new Database(
            $repository = new ContainerRepository(
                new Container(), 'db.'
            )
        );

        $this->setDatabaseConnectionsAndDrivers($db);
        $db->setDefaultConnection('sqlite');

        $db->statement('CREATE TABLE items (name VARCHAR(50))');

        // Change from in-memory to temporary database
        $db->setConnection('sqlite', [
            'driver' => 'sqlite',
            'database' => '',
        ]);

        // The change should make sure that this does not fail,
        // even that it is the same driver and same connection,
        // but since the connection variable have been changed.
        $db->statement('CREATE TABLE items (name VARCHAR(50))');
    }

    public function testRawQueries()
    {
        $db = $this->prepareDatabase();

        $statement = $db->raw("SELECT * FROM items WHERE name = 'foo'");

        $this->assertInstanceOf(Statement::class, $statement);
        $this->assertEquals("SELECT * FROM items WHERE name = 'foo'", $statement->toSql());

        $results = $statement->execute();

        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);
        $this->assertEquals('foo', $results[0]->name);
    }

    public function testRawQueriesWithParameters()
    {
        $db = $this->prepareDatabase();

        $statement = $db->raw('SELECT * FROM items WHERE name = ? OR name = ?', ['foo', 1]);

        $this->assertInstanceOf(Statement::class, $statement);
        $this->assertEquals('SELECT * FROM items WHERE name = ? OR name = ?', $statement->toSql());
        $this->assertEquals("SELECT * FROM items WHERE name = 'foo' OR name = 1", $statement->toSqlWithBindings());

        $results = $statement->execute();

        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);
        $this->assertEquals('foo', $results[0]->name);
    }

    public function testStatements()
    {
        $db = $this->prepareDatabase();

        $results = $db->statement("SELECT * FROM items WHERE name = 'foo'");

        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);
        $this->assertEquals('foo', $results[0]->name);
    }

    public function testStatementsWithParameters()
    {
        $db = $this->prepareDatabase();

        $results = $db->statement('SELECT * FROM items WHERE name = ?', ['foo']);

        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);
        $this->assertEquals('foo', $results[0]->name);
    }

    public function testGettingLastInsertedId()
    {
        $db = $this->prepareDatabase();

        $this->assertEquals(1, $db->getLastInsertedId());
    }

    public function testTruncatingTables()
    {
        $db = $this->prepareDatabase();

        $results = $db->statement('SELECT * FROM items');

        $this->assertCount(1, $results);

        $db->table('items')->truncate();

        $results = $db->statement('SELECT * FROM items');

        $this->assertCount(0, $results);
    }

    public function testSelectRows()
    {
        $db = $this->prepareDatabase();

        $results = $db->table('items')->get();

        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);
        $this->assertEquals('foo', $results[0]->name);

        $results = $db->table('items')->where('name', 'foo')->get();

        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);
        $this->assertEquals('foo', $results[0]->name);

        $results = $db->table('items')->select(['name'])->get();

        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);
        $this->assertEquals('foo', $results[0]->name);

        $result = $db->table('items')->first();

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertEquals('foo', $result->name);

        $result = $db->table('items')->last();

        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertEquals('foo', $result->name);
    }

    public function testInsertRows()
    {
        $db = $this->prepareDatabase();

        $results = $db->table('items')->get();

        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);

        $db->table('items')->insert([
            ['name' => 'bar'],
            ['name' => 'baz'],
        ]);

        $results = $db->table('items')->get();

        $this->assertTrue(is_array($results));
        $this->assertCount(3, $results);
    }

    public function testUpdateRows()
    {
        $db = $this->prepareDatabase();

        $results = $db->table('items')->get();

        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);
        $this->assertEquals('foo', $results[0]->name);

        $db->table('items')->where('name', 'foo')->update(['name' => 'bar']);

        $results = $db->table('items')->get();

        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);
        $this->assertEquals('bar', $results[0]->name);
    }

    public function testDeleteRows()
    {
        $db = $this->prepareDatabase();

        $results = $db->table('items')->get();

        $this->assertTrue(is_array($results));
        $this->assertCount(1, $results);

        $db->table('items')->where('name', 'foo')->delete();

        $results = $db->table('items')->get();

        $this->assertTrue(is_array($results));
        $this->assertCount(0, $results);
    }

    public function testBuilderGrammars()
    {
        $db = $this->prepareDatabase();

        $this->assertEquals('SELECT * FROM `items`', $db->table('items')->toSql());
        $this->assertEquals('SELECT * FROM `items` WHERE `name` = "foo"', $db->table('items')->where('name', 'foo')->toSql());
    }

    public function testOrderByRandom()
    {
        $db = $this->prepareDatabase();

        $this->assertEquals(
            'SELECT * FROM `items` ORDER BY RANDOM()',
            $db->table('items')->orderByRandom()->toSql()
        );
    }

    public function testSelectingColumnAsAlias()
    {
        $db = $this->prepareDatabase();

        $this->assertEquals(
            'SELECT `foo` as `bar`, `toast` FROM `items`',
            $db->table('items')->select([['foo', 'bar'], 'toast'])->toSql()
        );
    }

    public function testOverwritingRepository()
    {
        $db = $this->prepareDatabase();

        $this->assertInstanceOf(ContainerRepository::class, $db->getRepository());

        $db->setRepository(new ArrayRepository([]));

        $this->assertInstanceOf(ArrayRepository::class, $db->getRepository());
    }

    protected function prepareDatabase()
    {
        $db = new Database(
            $repository = new ContainerRepository(
                new Container(), 'db.'
            )
        );

        $this->setDatabaseConnectionsAndDrivers($db);
        $db->setDefaultConnection('sqlite');

        $this->runDatabaseMigrations($db);
        $this->runDatabaseSeeder($db);

        return $db;
    }

    protected function runDatabaseMigrations(Database $db)
    {
        $db->statement('CREATE TABLE `items` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` VARCHAR(55))');
    }

    protected function runDatabaseSeeder(Database $db)
    {
        $db->statement("INSERT INTO `items` (`name`) VALUES ('foo')");
    }
}
