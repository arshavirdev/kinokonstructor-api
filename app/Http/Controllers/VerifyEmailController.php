<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\UnauthorizedException;
use Laravel\Fortify\Contracts\VerifyEmailResponse;
use Laravel\Fortify\Http\Requests\VerifyEmailRequest;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class VerifyEmailController extends Controller
{
    public function request(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? new JsonResponse(['success' => true], 204)
                : redirect()->intended(config('front.base_url'));
        }

        $request->user()->sendEmailVerificationNotification();

        return $request->wantsJson()
            ? new JsonResponse('', 202)
            : redirect()->intended(config('front.base_url'));
    }

    public function verify(VerifyEmailRequest $request)
    {
        if (!$this->checkSignedUrl($request->integer('expires'), $request->input('signature')))
            throw new InvalidSignatureException();

        if ($request->user()->hasVerifiedEmail()) {
            return ['success' => true];
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return ['success' => true];
    }

    private static function getSignaturePayload(User $user, $expires)
    {
        return implode('|', [$user->id, $expires, $user->getEmailForVerification()]);
    }

    public static function getSignedUrl(User $user, $expires)
    {
        return config('front.base_url') . '/auth/verify-email?' . http_build_query([
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
                'expires' => $expires,
                'signature' => Hash::make(self::getSignaturePayload($user, $expires))
            ]);
    }

    public function checkSignedUrl($expires, $signature)
    {
        if (Carbon::now()->greaterThan(new Carbon($expires)))
            return false;
        
        return Hash::check(self::getSignaturePayload(Auth::user(), $expires), $signature);
    }
}
