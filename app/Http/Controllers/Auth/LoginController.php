<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        try {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $remember)) {
                $request->session()->regenerate();

                /** @var \App\Models\User $user */
                $user = Auth::user();
                
                if ($user->papel !== 'admin') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    throw ValidationException::withMessages([
                        'email' => 'Acesso restrito a administradores.',
                    ]);
                }

                return redirect()->intended('dashboard');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Check for specific error code or message if needed, but generic catch is safer for now
            // SQLSTATE[HY000]: General error: 1 no such table: usuarios
            
            throw ValidationException::withMessages([
                'email' => 'Erro de conexão com o banco de dados. Por favor, tente novamente mais tarde ou contate o suporte.',
            ]);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'email' => 'Ocorreu um erro inesperado ao tentar fazer login.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => 'As credenciais fornecidas estão incorretas.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
