<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;
use App\Models\User;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class SocialLoginController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }
 
    public function callback($provider)
    {
        $userInfo = Socialite::driver($provider)->user();
        $existingUser = User::where('email', $userInfo->email)->first();
        if (!$existingUser) {
            // Have no account
            try {
                DB::beginTransaction();
                // Create user
                $user = User::create([
                    'name' => $userInfo->name,
                    'email' => $userInfo->email
                ]);
                // Create provider
                $user->providers()->create([
                    'provider' => $provider
                ]);Auth::login($user, true);
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }
        } else {
            // Have an account, but have no the provider
            $checkProviders = false;
            foreach ($existingUser->providers as $providerItem) {
                if ($providerItem->provider == $provider) $checkProviders == true;
            }
            if (!$checkProviders) {
                $existingUser->providers()->create([
                    'provider' => $provider
                ]);
            }
            $user = $existingUser;
        }
        // Login
        Auth::login($user, true);

        return redirect()->route('home');
    }
}