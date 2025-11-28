<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InstitutionalController extends Controller
{
    public function index()
    {
        return view('institucional.index');
    }

    public function funcionalidades()
    {
        return view('institucional.funcionalidades');
    }

    public function contato(Request $request)
    {
        return view('institucional.contato');
    }
}

