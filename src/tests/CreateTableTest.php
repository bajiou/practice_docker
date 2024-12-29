<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../CreateTable.php');
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

final class CreateTableTest extends TestCase
{
    public function testConnectionSuccess()
    {



        $host = 'db';
        $user = 'root';
        $password = $_ENV['MYSQL_PASSWORD'];
        $dbName = $_ENV['MYSQL_DATABASE'];


        $result = connectDb($host, $user, $password, $dbName);
        $this->assertTrue($result, 'データベースアクセスが成功しませんでした。');
    }

    public function testConnectionFailure()
    {
        $host = 'db';
        $user = 'root';
        $password = 'wrong_pass';
        $dbName = 'test_database';

        $result = connectDb($host, $user, $password, $dbName);
        $this->assertFalse($result, 'データベースアクセスに成功してしまいました。');
    }
}
