<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index');
    }

    public function create(): View
    {
        return view('users.add');
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        if ($request->hasFile('photo')) {
            $data['img_url'] = upload_image($request->file('photo'), 'users');
        }

        $user = User::create($data);

        ActivityLog::record(
            'Pengguna baru ditambahkan',
            'users',
            $request->user()->name.' · '.$user->name.' ('.$user->role.')',
            'success'
        );

        return redirect()->route('users.index')
            ->with('toast', 'Pengguna '.$user->name.' berhasil ditambahkan!');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('photo')) {
            $data['img_url'] = upload_image($request->file('photo'), 'users');
        }

        $user->update($data);

        ActivityLog::record(
            'Data pengguna diperbarui',
            'users',
            $request->user()->name.' · '.$user->name,
            'primary'
        );

        return redirect()->route('users.index')
            ->with('toast', 'Data pengguna '.$user->name.' berhasil diperbarui!');
    }

    public function archive(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('users.index')
                ->with('toast', 'Anda tidak dapat meng-archive akun Anda sendiri.');
        }

        $user->archive();

        ActivityLog::record(
            'Akun pengguna di-archive',
            'users',
            $request->user()->name.' · '.$user->name,
            'error'
        );

        return redirect()->route('users.index')
            ->with('toast', 'Pengguna '.$user->name.' telah dipindahkan ke Archive.');
    }
}
