<?php

namespace App\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('index');
    }
}