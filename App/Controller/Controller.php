<?php

namespace App\Controller;

class Controller
{

    protected function render(string $view, array $data = []): void
    {
        extract($data);
        include __DIR__ . "/../Views/$view.php";
    }
}