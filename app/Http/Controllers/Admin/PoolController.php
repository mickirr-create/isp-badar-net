<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pool;
use App\Models\Router;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PoolController extends Controller
{
    public function index(Request $request)
    {
        $query = Pool::with('router');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pool_name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('router')) {
            $query->where('routers', $request->router);
        }

        $pools = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $routers = Router::orderBy('name')->get();

        return Inertia::render('Admin/Pools/Index', [
            'pools' => $pools,
            'routers' => $routers,
            'filters' => $request->only(['search', 'router']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Pools/Create', [
            'routers' => Router::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pool_name' => 'required|string|max:50',
            'routers' => 'required|string|max:50',
            'ip_address' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        Pool::create($validated);

        return redirect()->route('admin.pools.index')->with('success', 'IP Pool created successfully');
    }

    public function edit(Pool $pool)
    {
        return Inertia::render('Admin/Pools/Edit', [
            'pool' => $pool,
            'routers' => Router::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Pool $pool)
    {
        $validated = $request->validate([
            'pool_name' => 'required|string|max:50',
            'routers' => 'required|string|max:50',
            'ip_address' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $pool->update($validated);

        return redirect()->route('admin.pools.index')->with('success', 'IP Pool updated successfully');
    }

    public function destroy(Pool $pool)
    {
        $pool->delete();
        return redirect()->route('admin.pools.index')->with('success', 'IP Pool deleted successfully');
    }
}
