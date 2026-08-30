<?php

namespace Modules\Publishing\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class PublishingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('publishing::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('publishing::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string|int $id): View
    {
        return view('publishing::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string|int $id): View
    {
        return view('publishing::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string|int $id): RedirectResponse
    {
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string|int $id): RedirectResponse
    {
        return redirect()->back();
    }
}
