<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Dang nhap bang SDT + mat khau — du de biet AI xac nhan tung ho so. */
class AuthController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'phone.required' => 'Nhập số điện thoại.',
            'password.required' => 'Nhập mật khẩu.',
        ]);

        if (! Auth::attempt($cred + ['is_active' => true], $request->boolean('remember'))) {
            return back()->withErrors(['phone' => 'Số điện thoại hoặc mật khẩu không đúng.'])->onlyInput('phone');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('lots.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
