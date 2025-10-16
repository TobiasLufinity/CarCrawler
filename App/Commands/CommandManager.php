<?php

namespace App\Commands;

use RuntimeException;

class CommandManager
{

    private array $commands = [];

    public function register(string $name, string $class): void
    {
        $this->commands[$name] = $class;
    }

    public function list(): array
    {
        return array_keys($this->commands);
    }

    public function run(string $name, array $args = []): void
    {
        if (!isset($this->commands[$name])) {
            throw new RuntimeException("Command '$name' not found.");
        }

        $class = $this->commands[$name];
        $command = new $class();

        if (!method_exists($command, 'execute')) {
            throw new RuntimeException("Command class '$class' must have an execute() method.");
        }

        $command->execute($args);
    }
}