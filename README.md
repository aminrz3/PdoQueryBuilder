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
    'database' => 'db_name',
    'db_user' => 'db_user',
    'db_password' => 'db_password',
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

# Insert
```php
$pdoQueryBuilder->table('users')->insert([
    'name'=>'Amin',
    'Family'=>'Rahimzadeh',
    'Instagram'=>'amin.rz3',
    'Job'=>'Android,PHP Developer',
]);
```

# Update
```php
//update all columns
$response = $this->pdoQueryBuilder->table('users')
            ->update(['name' => 'Amin', 'Family' => 'Rahimzadeh']);

//update with where
$response = $this->pdoQueryBuilder->table('users')
            ->where(['name' => 'A', 'Family' => 'R'])
            ->update(['name' => 'Amin', 'Family' => 'Rahimzadeh']);
            
//if update success $response = 1
//if update failed $response = 0
```

# Delete
```php
$response = $this->pdoQueryBuilder->table('users')
            ->where(['name' => 'Amin', 'Family' => 'Rahimzadeh'])
            ->delete();
            
//if delete success $response = 1
//if delete failed $response = 0
```

# Where Syntax
## Basic
```php
// WHERE name = 'Amin'
$datas = $pdoQueryBuilder->table('users')
         ->where(['name'=>'Amin'])
         ->get();

// WHERE id > 50
$datas = $pdoQueryBuilder->table('users')
         ->where(['id[>]'=>50])
         ->get();
         
// WHERE id >= 50
$datas = $pdoQueryBuilder->table('users')
         ->where(['id[>=]'=>50])
         ->get();
         
// WHERE id < 50
$datas = $pdoQueryBuilder->table('users')
         ->where(['id[<]'=>50])
         ->get();
         
// WHERE id <= 50
$datas = $pdoQueryBuilder->table('users')
         ->where(['id[<=]'=>50])
         ->get();
         
// WHERE id != 50
$datas = $pdoQueryBuilder->table('users')
         ->where(['id[!]'=>50])
         ->get();
```

## AND-OR
```php
$datas = $pdoQueryBuilder->table('users')
         ->where([
            'OR'=>[
                 'name[!]'=>'Amin',
                 'Family[!]'=>'Rahimzadeh'
            ]])
         ->get();
// Where name!='Amin' OR Family!='Rahimzadeh'

$datas = $pdoQueryBuilder->table('users')
         ->where([
             'OR'=>[
                'name[!]'=>'Amin','
             AND'=>[
                'Family[!]'=>'Rahimzadeh',
                'instagram'=>'amin.rz3'
                ]
             ]
         ])
         ->get();

// Where name!='Amin' OR (Family!='Rahimzadeh' AND instagram=amin.rz3)
```

## Like
```php
$datas = $pdoQueryBuilder->table('users')
         ->where([
            'name[~]'=>'A'
         ])
         ->get();
// Where name LIKE '%A%'

$datas = $pdoQueryBuilder->table('users')
         ->where([
            'name[~]'=>[
                'OR'=>[
                    'A','AM'
                 ]
           ]
         ])
         ->get();
// Where name LIKE '%A%' OR LIKE '%AM%'
```

## ORDER


