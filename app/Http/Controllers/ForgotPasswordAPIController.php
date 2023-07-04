<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\ResetLinkEmail;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\View;

class ForgotPasswordAPIController extends Controller
{

    public function SendEmailForgotPass(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        $email = $user->email;

        if (!$user) {
            return response()->json(['error' => 'Email tidak ditemukan'], 404);
        }

        if (is_null($user->email_verified_at)) {
            return response()->json(['error' => 'Email tidak valid atau belum diverifikasi'], 400);
        }

        $token = Password::getRepository()->create($user);

        $resetLink = 'http://192.168.1.26:5173/reset-password';

        Mail::to($user->email)->send(new ResetLinkEmail($user, $resetLink));

        return response()->json([
            'message' => 'Email pengaturan ulang kata sandi telah dikirim',
            'email' => $email,
            'token' => $token
        ], 200);
    }


    public function ChangePassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = bcrypt($password);
                $user->save();
            }
        );

        if ($response == Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password berhasil direset Silahkan login kembali'], 200);
        } else {
            return response()->json(['error' => 'Gagal mereset password'], 400);
        }
    }
}
