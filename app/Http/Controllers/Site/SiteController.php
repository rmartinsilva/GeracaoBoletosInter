<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(): View
    {
        return view('site.index');
    }
}
