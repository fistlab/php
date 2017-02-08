# Fistlab Database

[![StyleCI](https://styleci.io/repos/67337527/shield?style=flat)](https://styleci.io/repos/67337527)
[![Build Status](https://travis-ci.org/fistphp/database.svg)](https://travis-ci.org/fistphp/database)
[![Total Downloads](https://poser.pugx.org/fist/database/d/total.svg)](https://packagist.org/packages/fist/database)
[![Latest Stable Version](https://poser.pugx.org/fist/database/v/stable.svg)](https://packagist.org/packages/fist/database)
[![Latest Unstable Version](https://poser.pugx.org/fist/database/v/unstable.svg)](https://packagist.org/packages/fist/database)
[![License](https://poser.pugx.org/fist/database/license.svg)](https://packagist.org/packages/fist/database)

The Fistlab Database component is a database toolkit, providing an expressive query builder. It currently supports MySQL and SQLite.

Languages: __php__.

## Installation

Install using Composer.
```
composer require fist/database
```

## Preparing

The constructor accepts an instance of [`RepositoryInterface`](https://github.com/fistphp/repository/blob/master/RepositoryInterface.php) from [`fist/repository`](https://github.com/fistphp/repository).   

Example

```
$db = new \Fist\Database\Database(
    $repository = new Fist\Repository\ArrayRepository([
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
    ])
);
```

> I have made more setup at this [gist](https://gist.github.com/marktopper/2f783901f19da6597b935e3b432fa41d).

## Usage

#### Running raw statements

Raw statements can be ran by using the `statement`-method.

```
$db->statement("SELECT * FROM `users` WHERE `username` = 'mark'");
```

It also takes an optional second argument with parameters to bind. Let's do the same query but by using bindings instead.

```
$db->statement("SELECT * FROM `users` WHERE `username` = ?", ['mark'])
```

#### Selecting all rows

Select all rows from a table using the query builder is quite easy.

```
$users = $db->table('users')->get();

foreach ($users as $user) {
    echo "Hello ".$user->username;
}
```

#### Select single row

Often you might want to get just a single database row object, like the current logged in user.

This can be done quite easy as well.

```
$user = $db->table('users')->first();

echo "Hello ".$user->username;
```

> Note that in case of no results. `null` will be returned. To get an exception instead use the `firstOrFail`-method.

#### Select specific columns

Want to select only specific columns, like `username`, `name` and `age`.

```
$db->table('users')->select(['username', 'name', 'age'])->get();
```

You can also use aliases for the selected columns, like you want to get `name` as `fullname`.

```
$db->table('users')->select(['username', ['name' => 'fullname'], 'age'])->get();
```

#### Where clauses

You can use where clauses to the query builder to filter your results.

##### Basic where clauses

By default the operator is `=` for where clauses.

```
$db->table('users')->where('username', 'mark')->first();
$db->table('users')->where('username', '=', 'mark')->first();
```

The two methods above will do exactly the same, however you can use a set of other operators.

```
$db->table('users')->where('username', '!=', 'mark')->first();
$db->table('users')->where('age', '>', 18)->first();
$db->table('users')->where('age', '<', 18)->first();
$db->table('users')->where('age', '>=', 18)->first();
$db->table('users')->where('age', '<=', 18)->first();
$db->table('users')->where('age', 'LIKE', 'ma%')->first();
```

The default behaviour of the where clauses are all using `and` for combining.

However you might want to use `or` for some situations.

```
$db->table('users')
    ->where('username', 'mark')
    ->orWhere('username', 'topper')
    ->first();
```

You mind want to group the where clauses in sub clauses.

```
$db->table('users')
    ->where('username', 'mark')
    ->orWhere(function ($query) {
        $query->where('username', 'topper')
            ->orWhere('name', 'Mark Topper')
    })
    ->first();
```

##### Where null

Want to use the where clause to filter value from that are not null.

```
$db->table('users')->whereNull('age')->get();
```

##### Where not null

Want to use the where clause to filter value from that are null.

```
$db->table('users')->whereNotNull('age')->get();
```

#### Joining

You can join additional tables using our joining methods.

##### Inner join table

```
$db->table('users')
    ->join('devices', 'users.id', '=', 'devices.user_id')
    ->get();
```

> By default the operator is `=` for join clauses.  
> So you can actually use `join('devices', 'users.id', 'devices.user_id')`

##### Outer join table

```
$db->table('users')
    ->outerJoin('devices', 'users.id', '=', 'devices.user_id')
    ->get();
```

##### Left join table

```
$db->table('users')
    ->leftJoin('devices', 'users.id', '=', 'devices.user_id')
    ->get();
```
##### Right join table

```
$db->table('users')
    ->rightJoin('devices', 'users.id', '=', 'devices.user_id')
    ->get();
```

##### Cross join table

```
$db->table('users')
    ->crossJoin('devices', 'users.id', '=', 'devices.user_id')
    ->get();
```

##### Advanced join clause

```
$db->table('users')
    ->join('devices', function ($join) {
        $join->on('users.id', '=', 'devices.user_id')
            ->where('devices.platform', 'ios');
    })
    ->get();
```

#### Order results

You can other by a column, while the second argument controls the direction of the sort and may be either `asc` or `desc`.

```
$db->table('users')
    ->orderBy('name', 'desc')
    ->get();
```

You can other by multiple columns.

```
$db->table('users')
    ->orderBy('fistname', 'desc')
    ->orderBy('lastname', 'desc')
    ->get();
```

##### Order by random

Randomize the order

```
$db->table('users')
    ->orderByRandom()
    ->first();
```

#### Grouping results

You can group the results.

```
$db->table('users')
    ->groupBy('country')
    ->get();
```

#### Limit results (& offset)

Limiting results with an offset are often used, specially when paginating.

```
$db->table('users')
    ->limit(100)
    ->offset(100)
    ->get();
```

#### Count results

Count rows easily

```
$users = $db->table('users')->count();
```

#### Raw expressions

Sometimes you may need to use a raw expression in a query.

```
$db->table('users')
    ->select([
        $db->raw('count(*) as user_count'),
        'status',
    ])
    ->groupBy('status')
    ->get();
```

#### Conditional clauses

Sometimes you might want to only run a certain part of your query when something is true.
You may for instance implement and `where` statement that only applies if a user is logged in.

```
$currentUserId = 1;
$loggedIn = true;

$db->table('users')
    ->when($loggedIn, function ($query) {
        $query->where('id', '=', $currentUserId);
    })
    ->get();
```

#### Insert rows

The `insert` method accepts an array of column names and values.

```
$db->table('users')->insert([
    ['email' => 'mark@example.com', 'username' => 'mark'],
    ['email' => 'john@example.com', 'username' => 'john'],
]);
```

Or you can insert a single row.

```
$db->table('users')->insert(
    ['email' => 'mark@example.com', 'username' => 'mark']
);
```

##### Auto incrementing IDs

Want to insert a row and get the auto incremented ID? You can do this using the `insertGetId` method.

```
$id = $db->table('users')->insertGetId(
    ['email' => 'mark@example.com', 'username' => 'mark']
);
```

#### Update rows

Update `name` for the for the user with the `username` set to `mark`?

```
$db->table('user')->where('username', 'mark')->update(['name' => 'Foobar']);
```

#### Deleting rows

Deleting rows have never been easier.

```
$db->table('users')->where('last_login', '<', '2016-01-01 00:00:00')->delete();
```

If you wish to truncate the entire table, which will remove all rows and reset the auto-incrementing ID to zero, you may use the `truncate` method.

```
$db->table('users')->truncate();
```

#### Connection swapping

Have multiple connections configured you may swap between connections. The default connection is used unless anything else specified.

```
$db->connection('connection-name')
    ->table('users')
    ->get();
```
