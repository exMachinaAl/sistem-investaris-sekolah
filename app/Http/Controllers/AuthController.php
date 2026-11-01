<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class AuthController {
    public function show(){return view('auth.login');}
    public function login(Request $request){
        $data=$request->validate(['email'=>['required','email'],'password'=>['required','string']]);
        if(!Auth::attempt($data)){
            throw ValidationException::withMessages(['email'=>'Email atau password tidak sesuai.']);
        }
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }
    public function logout(Request $request){
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
