<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    public function showVerifyOtpForm()
    {
        $user = Auth::user();
        $email = $user->email;
        return view('auth.verify-otp', compact('email'));
    }

    public function sendOtp(Request $request)
    {
        $user = Auth::user();
        $otp = rand(100000, 999999); // Gera um OTP de 6 dígitos

        User::where('id', $user->id)->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(30), // Expira em 30 minutos
        ]);

        // Envia o OTP por email (substitua pelo seu próprio método de envio)
        /*Mail::raw("Seu código OTP é: $otp", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Seu código OTP');
        });*/

        return back()->with('message', 'Código OTP enviado para seu email.');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|integer']);

        $user = Auth::user();
        $empresa = $user->empresas->first();

        if ($user->otp == $request->otp && Carbon::now()->lt(Carbon::parse($user->otp_expires_at))) {

            User::where('id', $user->id)->update([
                'otp' => null,
                'otp_expires_at' => null,
                'otp_verified_at' => now(),
            ]);

            return redirect()->route('empresas.edit', $empresa->id)->with('message', 'Código OTP verificado com sucesso!');
        } else {
            return back()->withErrors(['otp' => 'Código OTP inválido ou expirado.']);
        }
    }
}
