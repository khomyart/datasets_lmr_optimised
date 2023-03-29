<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\AuthAPI;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\User;
use App\Models\AccessToken;

class UserController extends Controller
{
    public function create(Request $request) {
        $data = $request->validate([
            "name" => 'required|string|max:250',
            "email" => 'required|string|unique:users|max:250',
            "password" => 'required|string|confirmed|max:250',
        ]);

        $user = User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => Hash::make($data["password"]),
        ]);

        if ($user != null) {
            return response($user);
        } else {
            return response("FAIL", 404);
        }
    }

    public function authenticate(Request $request) {
        $credentials = $request->validate([
            "email" => "required|email",
            "password" => "required|string",
        ]);
        $user = User::firstWhere("email", $credentials["email"]);

        if (isset($user) && Hash::check($credentials["password"], $user->password)) {
            $auth = new AuthAPI(user: $user, ip: $request->ip());

            $token = $auth->hasToken() ? $auth->recreateAccessToken() : $auth->createAccessToken();

            $userData = $user->toArray();
            unset($userData["access_token"]);

            return response()->json(["user" => $userData, "auth" => $token->toArray()]);
        } else {
            return response("Невірні данні аутентифікації", 403);
        }
    }

    public function logout(Request $request) {
        $auth = AuthAPI::isAuthenticated($request->bearerToken(), $request->ip());

        if ($auth) {
            $auth->hasToken()->delete();
        }

        return response("OK", 200);
    }
}
