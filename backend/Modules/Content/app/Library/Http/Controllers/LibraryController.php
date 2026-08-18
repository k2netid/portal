<?php

declare(strict_types=1);

namespace Modules\Content\Library\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Factory|View
    {
        return view('library::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Factory|View
    {
        return view('library::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): void {}

    /**
     * Show the specified resource.
     */
    public function show(string $id): Factory|View
    {
        return view('library::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Factory|View
    {
        return view('library::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): void {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): void {}
}
