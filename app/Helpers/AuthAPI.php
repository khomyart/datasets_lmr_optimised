<?php
namespace App\Helpers;

use App\Models\AccessToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthAPI {
    //minutes
    const TOKEN_LIFE_TIME = 180;
    const AWAY_FROM_KEYBOARD_TIME = 20;

    public $user;
    public $tokenTime;
    public $ip;

    function __construct($user, $ip, $tokenTime = self::TOKEN_LIFE_TIME) {
        $this->user = $user;
        $this->tokenTime = $tokenTime;
        $this->ip = $ip;
    }

    public static function isAuthenticated($bearerToken, $ip) {
        $token = AccessToken::firstWhere('token', $bearerToken);

        if (isset($token)) {
            return new self(user: $token->user, ip: $ip);
        }

        return false;
    }

    public function hasToken() {
        $token = $this->user->accessToken;
        return isset($token) ? $token : false;
    }

    public function isTokenExpired() {
        $now = Carbon::now();
        $tokenExpiringTime = new Carbon($this->hasToken()->expired_at);

        return $now > $tokenExpiringTime;
    }

    public function isUserAway($afkTime = self::AWAY_FROM_KEYBOARD_TIME) {
        $now = Carbon::now();
        $tokenLastUsedTime = new Carbon($this->hasToken()->last_used);

        return $now > $tokenLastUsedTime->addMinutes($afkTime);
    }

    public function createAccessToken($tokenLifeTime = self::TOKEN_LIFE_TIME) {
        $accessToken = AccessToken::create([
            'user_id' => $this->user->id,
            'token' => Hash::make(today()),
            'ip_address' => $this->ip,
            'last_used' => Carbon::now(),
            'expired_at' => Carbon::now()->addMinutes($tokenLifeTime),
        ]);

        return isset($accessToken) ? $accessToken : false;
    }

    public function refreshAccessToken($tokenLifeTime = self::TOKEN_LIFE_TIME) {
        $accessToken = $this->user->accessToken;

        $accessToken->last_used = Carbon::now();
        $accessToken->expired_at = Carbon::now()->addMinutes($tokenLifeTime);

        $accessToken->save();

        return $accessToken;
    }

    public function recreateAccessToken($tokenLifeTime = self::TOKEN_LIFE_TIME) {
        $accessToken = $this->user->accessToken;

        $accessToken->token = Hash::make(today());
        $accessToken->last_used = Carbon::now();
        $accessToken->expired_at = Carbon::now()->addMinutes($tokenLifeTime);

        $accessToken->save();

        return $accessToken;
    }

    public function isAuthorizedFor($action, $section) {
        $allowensesList = $this->getAllowenses();

        $isAuthorized = false;
        foreach($allowensesList as $allowense) {
            if ($allowense->action == $action && $allowense->section == $section) {
                $isAuthorized = true;
                break;
            }
        }

        return $isAuthorized;
    }
}
