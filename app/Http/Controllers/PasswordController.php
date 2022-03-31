<?php

namespace App\Http\Controllers;

use App\Traits\ResetsPasswords;

class PasswordController extends Controller
{
    use ResetsPasswords;

    public function __construct()
    {
        $this->broker = 'users';
    }
}
