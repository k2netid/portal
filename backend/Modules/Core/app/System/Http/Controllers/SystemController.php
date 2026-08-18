<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Factory|View
    {
        return view('system::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Factory|View
    {
        return view('system::create');
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
        return view('system::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Factory|View
    {
        return view('system::edit');
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
