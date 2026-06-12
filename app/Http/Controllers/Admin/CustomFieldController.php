<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class CustomFieldController extends Controller
{
    private string $configPath;

    public function __construct()
    {
        $this->configPath = storage_path('app/customer_fields.json');
    }

    public function index()
    {
        $fields = $this->getFields();

        return Inertia::render('Admin/CustomFields/Index', [
            'fields' => $fields,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fields' => 'required|array',
            'fields.*.name' => 'required|string|max:50',
            'fields.*.type' => 'required|string|in:text,select,textarea,checkbox,number',
            'fields.*.placeholder' => 'nullable|string|max:100',
            'fields.*.options' => 'nullable|string',
            'fields.*.required' => 'required|boolean',
            'fields.*.show_on_register' => 'required|boolean',
        ]);

        File::put($this->configPath, json_encode($validated['fields'], JSON_PRETTY_PRINT));

        return back()->with('success', 'Field kustom berhasil disimpan');
    }

    public function destroy(Request $request)
    {
        $index = $request->integer('index');
        $fields = $this->getFields();

        if (isset($fields[$index])) {
            array_splice($fields, $index, 1);
            File::put($this->configPath, json_encode($fields, JSON_PRETTY_PRINT));
        }

        return back()->with('success', 'Field berhasil dihapus');
    }

    private function getFields(): array
    {
        if (!File::exists($this->configPath)) {
            return [];
        }

        return json_decode(File::get($this->configPath), true) ?? [];
    }
}
