<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RolePasswordStore;

class PasswordController extends Controller
{
    public function form()
    {
        // Hanya admin boleh mengubah password role
        if (session('role') !== 'admin') {
            return redirect()->route('dashboard')->with('err','Hanya admin yang boleh mengubah password.');
        }
        return view('auth.password');
    }

    public function update(Request $r)
    {
        if (session('role') !== 'admin') {
            return redirect()->route('dashboard')->with('err','Hanya admin yang boleh mengubah password.');
        }

        $data = $r->validate([
            'target_role'  => 'required|in:admin,kepsek',
            'new_password' => 'required|string|min:6|confirmed', // butuh new_password_confirmation
        ]);

        /** @var RolePasswordStore $store */
        $store = app(RolePasswordStore::class);
        $store->set($data['target_role'], $data['new_password']);

        return back()->with('ok','Password untuk role "'.$data['target_role'].'" berhasil diubah.');
    }
}
