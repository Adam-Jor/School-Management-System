<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load(['student.schoolClass', 'student.grades.subject', 'student.attendances', 'teacher.subject']);

        return view('profile.index', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('tbl_users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'regex:/^[0-9+\-\s]{9,16}$/'],
            'password' => ['nullable', 'string', 'min:6'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'phone.regex' => 'Format nomor telepon hanya boleh berisi angka.',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('photo')) {
            $validated['img_url'] = upload_image($request->file('photo'), 'users');
        }

        $user->update($validated);

        ActivityLog::record(
            'Profil pribadi diperbarui',
            'profile',
            $user->name.' ('.$user->role.')',
            'primary'
        );

        return redirect()->route('profile.show')
            ->with('toast', 'Profil Anda berhasil diperbarui!');
    }
}
