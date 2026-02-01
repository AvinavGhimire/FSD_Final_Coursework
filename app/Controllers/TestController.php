<?php

namespace App\Controllers;

use App\Core\Controller;

class TestController extends Controller
{
    public function routes()
    {
        $this->view('test/routes');
    }
}