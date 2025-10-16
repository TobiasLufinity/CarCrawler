<?php

declare(strict_types=1);

namespace App\Model;

use App\Helpers\Config;
use mysqli;

class Database
{

    protected mysqli $connection;
    private static ?Database $instance = null;
    public function __construct()
    {
        $config = Config::getInstance();

        $this->connection = new mysqli(
            $config->get('db_host'),
            $config->get('db_user'),
            $config->get('db_pass'),
            $config->get('db_name')
        );

        if ($this->connection->connect_error) {
            die("Database connection failed: " . $this->connection->connect_error);
        }
    }

    public static function getInstance(): ?Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }
}