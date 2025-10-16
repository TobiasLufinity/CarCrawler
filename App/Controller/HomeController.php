<?php

namespace App\Controller;

use App\Model\CarRepository;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('index');
    }
}