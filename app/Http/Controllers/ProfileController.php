<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load([
            'student.schoolClass',
            'student.grades.subject',
            'student.attendances',
            'teacher.subject',
            'teacher.schoolClasses',
        ]);

        return view('profile.index', compact('user'));
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

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
