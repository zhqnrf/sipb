<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RolePasswordStore;

class AuthController extends Controller
{
    public function login(Request $r)
    {
        $r->validate([
            'role' => 'required|in:admin,kepsek',
            'password' => 'required|string',
        ]);

        /** @var RolePasswordStore $store */
        $store = app(RolePasswordStore::class);

        if (!$store->check($r->role, $r->password)) {
            return back()->withInput()->with('err','Role / Password salah.');
        }

        session(['role' => $r->role]);
        return redirect()->route('dashboard')->with('ok','Login berhasil sebagai '.$r->role);
    }

 public function logout()
{
    session()->forget('role');
    return redirect()->route('login')->with('ok','Anda sudah logout.');
}

}
