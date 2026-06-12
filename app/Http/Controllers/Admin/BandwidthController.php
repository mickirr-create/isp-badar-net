<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bandwidth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BandwidthController extends Controller
{
    public function index(Request $request)
    {
        $query = Bandwidth::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name_bw', 'like', "%{$search}%");
        }

        $bandwidths = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Bandwidth/Index', [
            'bandwidths' => $bandwidths,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Bandwidth/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_bw' => 'required|string|max:50',
            'rate_down' => 'required|numeric|min:0',
            'rate_up' => 'required|numeric|min:0',
            'rate_down_unit' => 'nullable|string|max:10',
            'rate_up_unit' => 'nullable|string|max:10',
            'burst' => 'nullable|string|max:20',
        ]);

        Bandwidth::create($validated);

        return redirect()->route('admin.bandwidth.index')->with('success', 'Bandwidth created successfully');
    }

    public function edit(Bandwidth $bandwidth)
    {
        return Inertia::render('Admin/Bandwidth/Edit', [
            'bandwidth' => $bandwidth,
        ]);
    }

    public function update(Request $request, Bandwidth $bandwidth)
    {
        $validated = $request->validate([
            'name_bw' => 'required|string|max:50',
            'rate_down' => 'required|numeric|min:0',
            'rate_up' => 'required|numeric|min:0',
            'rate_down_unit' => 'nullable|string|max:10',
            'rate_up_unit' => 'nullable|string|max:10',
            'burst' => 'nullable|string|max:20',
        ]);

        $bandwidth->update($validated);

        return redirect()->route('admin.bandwidth.index')->with('success', 'Bandwidth updated successfully');
    }

    public function destroy(Bandwidth $bandwidth)
    {
        $bandwidth->delete();
        return redirect()->route('admin.bandwidth.index')->with('success', 'Bandwidth deleted successfully');
    }
}
