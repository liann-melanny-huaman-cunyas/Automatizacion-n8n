<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('usuario_id')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $datos = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $usuario = Usuario::where('correo', $datos['correo'])
            ->where('estado', 'activo')
            ->first();

        if (!$usuario || !Hash::check($datos['password'], $usuario->password)) {
            return back()
                ->withInput($request->only('correo'))
                ->withErrors([
                    'correo' => 'El correo o la contraseña son incorrectos.',
                ]);
        }

        $request->session()->regenerate();

        session([
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre,
            'usuario_rol' => $usuario->rol,
        ]);

        $usuario->ultimo_acceso = now();
        $usuario->save();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}