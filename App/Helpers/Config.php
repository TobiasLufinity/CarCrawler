<?php

declare(strict_types=1);

namespace App\Helpers;

use RuntimeException;

class Config
{
    private static ?Config $instance = null;
    private array $data = [];

    public function __construct(string $configPath)
    {
        if (!file_exists($configPath)) {
            throw new RuntimeException("Config file not found: $configPath");
        }

        $data = require $configPath;

        if (!is_array($data)) {
            throw new RuntimeException("Invalid config file format.");
        }

        $this->data = $data;
    }

    public static function getInstance(string $configFile = __DIR__ . '/../../config.php'): self
    {
        if (self::$instance === null) {
            self::$instance = new self($configFile);
        }
        return self::$instance;
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }
}