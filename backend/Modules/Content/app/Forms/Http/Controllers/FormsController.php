<?php

declare(strict_types=1);

namespace Modules\Content\Forms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FormsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Factory|View
    {
        return view('forms::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Factory|View
    {
        return view('forms::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $_request): RedirectResponse
    {
        return redirect()->back();
    }

    /**
     * Show the specified resource.
     */
    public function show(string $_id): Factory|View
    {
        return view('forms::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $_id): Factory|View
    {
        return view('forms::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $_request, string $_id): RedirectResponse
    {
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $_id): RedirectResponse
    {
        return redirect()->back();
    }
}
