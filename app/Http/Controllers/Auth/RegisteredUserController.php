<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Configuration;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $config = Configuration::query()->select('agreement')->first();
        $branches = Branch::all();
        return view('auth.register')->with(compact( 'config', 'branches'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'checkbox' => ['required'],
            'surname' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'branch' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:16', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (Str::contains($request->login, '_')){
            return redirect()->back()->with('error', 'Неверный номер, пожалуйста, перепроверьте');
        }

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'city' => $request->city,
            'login' => $request->login,
            'password' => $request->password,
            'branch' => $request->branch,
            'code' => $this->generateCode(),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    public function generateCode ()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $code = '';
            for ($i = 0; $i < 4; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
            $exists = User::query()->where('code', $code)->exists();
        } while ($exists);

        return $code;
    }
}
