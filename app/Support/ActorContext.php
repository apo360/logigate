<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ActorContext
{
    public static function user(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public static function id(): ?int
    {
        return self::user()?->id;
    }

    public static function primaryRole(): ?string
    {
        return self::user()?->roles->pluck('name')->first();
    }

    public static function requestUrl(): ?string
    {
        return request()?->fullUrl();
    }

    public static function ipAddress(): ?string
    {
        return request()?->ip();
    }

    public static function userAgent(): ?string
    {
        return request()?->header('User-Agent');
    }
}
