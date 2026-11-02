<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Página inicial do site
     */
    public function index()
    {
        return view('site.index');
    }

    /**
     * Página sobre nós
     */
    public function sobre()
    {
        return view('site.sobre');
    }

    /**
     * Página de serviços
     */
    public function servicos()
    {
        return view('site.servicos');
    }

    /**
     * Página de portfólio
     */
    public function portfolio()
    {
        return view('site.portfolio');
    }

    /**
     * Página de contato
     */
    public function contato()
    {
        return view('site.contato');
    }
}
