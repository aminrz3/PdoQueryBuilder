<?php


namespace Tests\Unit;


use App\Exceptions\ConfigValidException;
use App\Exceptions\DatabaseConnectionException;
use App\Helpers\Config;
use App\Database\PDODatabaseConnection;
use App\Contracts\DatabaseConnectionInterface;
use PHPUnit\Framework\TestCase;
use PDO;

class PDODatabaseConnectionTest extends TestCase
{

    public function testPDODatabaseConnectionImplementDatabaseConnectionInterface()
    {
        $config = $this->getConfigs();
        $pdoConnection = new PDODatabaseConnection($config);

        $this->assertInstanceOf(DatabaseConnectionInterface::class, $pdoConnection);
    }

    public function testConnectMethodShouldBeReturnInstance(){
        $config = $this->getConfigs();
        $pdoConnection = new PDODatabaseConnection($config);

        $pdoHandler = $pdoConnection->connect();

        $this->assertInstanceOf(PDODatabaseConnection::class,$pdoHandler);

        return $pdoHandler;
    }

    /**
     * @depends testConnectMethodShouldBeReturnInstance
     *
     */
    public function testConnectMethodShouldBeConnectToDatabase($pdoHandler){
        $this->assertInstanceOf(PDO::class,$pdoHandler->getConnection());
    }

    public function testItThrowExceptionIfConfigInvalid(){

        $this->expectException(DatabaseConnectionException::class);

        $config = $this->getConfigs();
        $config['database'] = "hey Bug";
        $pdoConnection = new PDODatabaseConnection($config);
        $pdoConnection->connect();
    }

    public function testItThrowExceptionNumberKeyConfig(){
        $this->expectException(ConfigValidException::class);
        $config = $this->getConfigs();
        unset($config['db_user']);
        $pdoConnection = new PDODatabaseConnection($config);
        $pdoConnection->connect();
    }

    private function getConfigs()
    {
        $configs = Config::get('database','pdo_testing');
        return $configs;
    }
}