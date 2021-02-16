<?php

namespace Tests\Unit;

use App\Exceptions\ConfigFileNotFoundException;
use App\Helpers\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase {

    public function testGetFileContentsReturnsArray(){
        $config = Config::getFileContents("database");

        $this->assertIsArray($config);
    }

    public function testItThrowsExceptionIfFileNotFound(){
        $this->expectException(ConfigFileNotFoundException::class);
        $config = Config::getFileContents("jimy");
    }

    public function testMethodReturnValidData(){
        $config = Config::get("database","pdo");

        $ExpectedData = [
            'driver'=>'mysql',
            'host'=>'localhost',
            'database'=>'orm_db',
            'db_user'=>'root',
            'db_password'=>'',
        ];

        $this->assertEquals($config,$ExpectedData);
    }
}
