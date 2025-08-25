<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

   public function store(Request $request): RedirectResponse
{
   $request->validate([
    'email' => [
        'required',
        'string',
        'email',
        'max:255',
        Rule::unique('oracle.USERS', 'EMAIL') 
    ],
    'USERNAME' => [
        'required',
        'string',
        'max:255',
        Rule::unique('oracle.USERS', 'USERNAME') 
    ],
    'USER_PASSWORD' => [
        'required',
        'string',
        'min:8',
    ],
]);


    $user = User::create([
        'NAME'           => $request->name,
        'USERNAME'       => $request->username,
        'EMAIL'          => $request->email,
        'USER_PASSWORD'  => Hash::make($request->password),
        'REMEMBER_TOKEN' => Str::random(60),
    ]);

    event(new Registered($user));
    Auth::login($user);

    return redirect()->intended(route('dashboard'));
}

}
