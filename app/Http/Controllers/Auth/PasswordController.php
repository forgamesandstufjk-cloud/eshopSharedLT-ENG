<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ],
            [
                'current_password.required' => 'Įveskite dabartinį slaptažodį.',
                'current_password.current_password' => 'Dabartinis slaptažodis neteisingas.',
                'password.required' => 'Įveskite naują slaptažodį.',
                'password.confirmed' => 'Slaptažodžio patvirtinimas nesutampa.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $newPassword = $request->input('password');

            if (
                filled($newPassword) &&
                Hash::check($newPassword, $request->user()->slaptazodis)
            ) {
                $validator->errors()->add(
                    'password',
                    'Naujas slaptažodis turi skirtis nuo dabartinio slaptažodžio.'
                );
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator, 'updatePassword');
        }

        $request->user()->update([
            'slaptazodis' => Hash::make($request->input('password')),
        ]);

        return back()->with('status', 'password-updated');
    }
}
