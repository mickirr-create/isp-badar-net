<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Router;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RouterController extends Controller
{
    public function index(Request $request)
    {
        $query = Router::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $routers = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Routers/Index', [
            'routers' => $routers,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Routers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tbl_routers,name',
            'ip_address' => 'required|string|max:50',
            'username' => 'nullable|string|max:50',
            'password' => 'nullable|string|max:100',
            'community' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'enabled' => 'nullable|boolean',
            'type' => 'nullable|string|max:20',
        ]);

        $validated['enabled'] = isset($validated['enabled']) ? 1 : 0;
        $validated['status'] = 'offline';

        Router::create($validated);

        return redirect()->route('admin.routers.index')->with('success', 'Router created successfully');
    }

    public function edit(Router $router)
    {
        return Inertia::render('Admin/Routers/Edit', [
            'router' => $router,
        ]);
    }

    public function update(Request $request, Router $router)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tbl_routers,name,' . $router->id,
            'ip_address' => 'required|string|max:50',
            'username' => 'nullable|string|max:50',
            'password' => 'nullable|string|max:100',
            'community' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'enabled' => 'nullable|boolean',
            'type' => 'nullable|string|max:20',
        ]);

        $validated['enabled'] = isset($validated['enabled']) ? 1 : 0;

        $router->update($validated);

        return redirect()->route('admin.routers.index')->with('success', 'Router updated successfully');
    }

    public function destroy(Router $router)
    {
        $router->delete();
        return redirect()->route('admin.routers.index')->with('success', 'Router deleted successfully');
    }
}
