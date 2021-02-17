# PDO-Mysql-ORM


## Get Started
### Install via composer
```
$ composer require amin.rz3/pdo-query-builder
```

### Load PDO Query Builder
```php
require 'vendor/autoload.php';

use App\Database\PDODatabaseConnection;
use App\Database\PDOQueryBuilder;
use App\Exceptions\ConfigValidException;
use App\Exceptions\DatabaseConnectionException;


$config = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'orm_db_testing',
    'db_user' => 'root',
    'db_password' => '',
];

try {
    $databaseConnection = new PDODatabaseConnection($config);
} catch (ConfigValidException $e) {
}

try {
    $pdoQueryBuilder = new PDOQueryBuilder($databaseConnection->connect());
} catch (DatabaseConnectionException $e) {
}
```

# Select
```php
//select all columns
$datas = $pdoQueryBuilder->table('users')->get();

//select two column
$datas = $pdoQueryBuilder->table('users')->get(['name','family']);

//select with where
$datas = $pdoQueryBuilder->table('users')->where(['instagram'=>'amin.rz3'])
         ->get(['name','family']);
         
```

