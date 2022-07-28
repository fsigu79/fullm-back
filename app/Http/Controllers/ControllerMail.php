<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ControllerMail extends Controller
{
     function index()
    {
     return view('mail.invoice');
    }
}
