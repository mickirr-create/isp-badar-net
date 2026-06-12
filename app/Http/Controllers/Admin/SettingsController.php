<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = AppConfig::pluck('value', 'setting')->toArray();

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string',
            'default_language' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'date_format' => 'nullable|string|max:20',
            'tax' => 'nullable|numeric|min:0|max:100',
            'tax_enabled' => 'nullable|boolean',
        ]);

        foreach ($validated as $key => $value) {
            AppConfig::setSetting($key, $value);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan');
    }

    public function localization()
    {
        $settings = AppConfig::pluck('value', 'setting')->toArray();

        return Inertia::render('Admin/Settings/Localization', [
            'settings' => $settings,
        ]);
    }

    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('fullname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Settings/Users', [
            'users' => $users,
            'filters' => $request->only(['search']),
        ]);
    }

    public function usersCreate()
    {
        return Inertia::render('Admin/Settings/UserForm');
    }

    public function usersStore(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:48|unique:tbl_users,username',
            'fullname' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'user_type' => 'required|string|in:SuperAdmin,Admin,Report,Agent,Sales',
            'status' => 'required|string|in:Active,Disabled',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['created_at'] = now();

        User::create($validated);

        return redirect()->route('admin.settings.users')->with('success', 'User admin berhasil dibuat');
    }

    public function usersEdit(User $user)
    {
        return Inertia::render('Admin/Settings/UserForm', [
            'editUser' => $user,
        ]);
    }

    public function usersUpdate(Request $request, User $user)
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'user_type' => 'required|string|in:SuperAdmin,Admin,Report,Agent,Sales',
            'status' => 'required|string|in:Active,Disabled',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $validated['password'] = bcrypt($request->password);
        }

        $user->update($validated);

        return redirect()->route('admin.settings.users')->with('success', 'User admin berhasil diperbarui');
    }

    public function usersDestroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.settings.users')->with('success', 'User admin berhasil dihapus');
    }

    public function dbStatus()
    {
        $tables = [];
        $dbConfig = config('database.connections.mysql.database');
        $pdo = \DB::connection()->getPdo();

        $stmt = $pdo->prepare("SHOW TABLE STATUS FROM `{$dbConfig}`");
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $tables[] = [
                'name' => $row['Name'],
                'engine' => $row['Engine'] ?? '',
                'rows' => $row['Rows'] ?? 0,
                'data_size' => $row['Data_length'] ?? 0,
                'index_size' => $row['Index_length'] ?? 0,
                'auto_increment' => $row['Auto_increment'] ?? null,
                'collation' => $row['Collation'] ?? '',
            ];
        }

        return Inertia::render('Admin/Settings/DbStatus', [
            'tables' => $tables,
            'database' => $dbConfig,
        ]);
    }

    public function dbBackup()
    {
        $dbConfig = config('database.connections.mysql.database');
        $pdo = \DB::connection()->getPdo();

        $stmt = $pdo->prepare("SHOW TABLE STATUS FROM `{$dbConfig}`");
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $tables = array_map(fn($r) => $r['Name'], $rows);

        return Inertia::render('Admin/Settings/DbBackup', [
            'tables' => $tables,
        ]);
    }

    public function dbBackupDownload(Request $request)
    {
        $request->validate([
            'tables' => 'required|array',
            'tables.*' => 'string',
        ]);

        $backup = [];
        foreach ($request->tables as $table) {
            $backup[$table] = \DB::table($table)->get()->toArray();
        }

        $filename = 'backup_' . date('Y-m-d_His') . '.json';

        return response()->json($backup)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Content-Type', 'application/json');
    }

    public function maintenance()
    {
        $settings = AppConfig::pluck('value', 'setting')->toArray();

        return Inertia::render('Admin/Settings/Maintenance', [
            'settings' => $settings,
        ]);
    }

    public function maintenanceToggle(Request $request)
    {
        $enabled = $request->boolean('enabled', false);
        AppConfig::setSetting('maintenance_mode', $enabled ? '1' : '0');

        if ($enabled) {
            Artisan::call('down');
        } else {
            Artisan::call('up');
        }

        return back()->with('success', $enabled ? 'Mode maintenance diaktifkan' : 'Mode maintenance dinonaktifkan');
    }
}
