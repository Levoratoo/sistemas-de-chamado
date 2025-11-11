<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            abort_unless($user && ($user->isAdmin() || $user->isGestor()), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $query = User::with(['role', 'areas']);
        
        // Busca por nome
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                  ->orWhere('login', 'like', '%' . $searchTerm . '%');
            });
        }
        
        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        $roles = Role::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles', 'areas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'login' => ['required', 'string', 'max:60', 'unique:users,login'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'area_ids' => ['array'],
            'area_ids.*' => [Rule::exists('areas', 'id')],
        ]);

        $data['login'] = strtolower(trim($data['login']));

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'login' => $data['login'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
        ]);

        $user->areas()->sync($data['area_ids'] ?? []);

        return redirect()->route('admin.users.index')->with('success', 'Usuario criado com sucesso.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'login' => ['required', 'string', 'max:60', Rule::unique('users', 'login')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'area_ids' => ['array'],
            'area_ids.*' => [Rule::exists('areas', 'id')],
        ]);

        $data['login'] = strtolower(trim($data['login']));

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'login' => $data['login'],
            'role_id' => $data['role_id'],
        ]);

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $user->areas()->sync($data['area_ids'] ?? []);

        return redirect()->route('admin.users.index')->with('success', 'Usuario atualizado com sucesso.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'Nao e possivel excluir o proprio usuario.');

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario removido com sucesso.');
    }
}

