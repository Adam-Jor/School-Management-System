<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class DesignSystemController extends Controller
{
    public function index(): View
    {
        return view('design-system.index', [
            'title' => 'Design System & UI Primitives',
            'breadcrumbs' => [
                ['label' => 'Design System'],
            ],
        ]);
    }
}
