<?php


namespace App\Database;

use App\Contracts\DatabaseConnectionInterface;
use App\Exceptions\DatabaseConnectionException;
use App\Exceptions\ConfigValidException;
use PDO;
use PDOException;
class PDODatabaseConnection implements DatabaseConnectionInterface
{

    protected $config;
    protected $connection;
    CONST REQUIRED_CONFIG_KEY=[
        'driver',
        'host',
        'database',
        'db_user',
        'db_password'
    ];
    public function __construct(array $config)
    {
        if(!$this->isValidConfig($config)){
            throw new ConfigValidException();
        }

        $this->config = $config;
    }

    public function connect()
    {
        $dsn = $this->generateDsn($this->config);

        try {
            $this->connection = new PDO(...$dsn);

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            throw new DatabaseConnectionException();
        }

        return $this;
    }

    public function getConnection()
    {
        return $this->connection;
    }


    private function generateDsn(array $config)
    {
        $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']}";

        return [
            $dsn,
            $config['db_user'],
            $config['db_password']
        ];
    }

    private function isValidConfig(array $config){
        $matches = array_intersect(self::REQUIRED_CONFIG_KEY,array_keys($config));

        return count($matches) === count(self::REQUIRED_CONFIG_KEY);
    }
}