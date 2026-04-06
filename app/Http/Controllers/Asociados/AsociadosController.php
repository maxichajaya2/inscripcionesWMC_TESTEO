<?php

namespace App\Http\Controllers\Asociados;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AsociadosController extends Controller
{
    public function index()
    {
         return Inertia::render('Asociados/Index');
    }
}
