<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Inertia\Inertia;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function () {
            return Limit::none();
        });

        //login
        Fortify::loginView(function () {
            return Inertia::render('Auth/Login');
        });

        Fortify::verifyEmailView(function () {
            return Inertia::render('Auth/VerifyEmail');
        });

        Fortify::authenticateUsing(function (Request $request) {
            $login = strtolower($request->input('email'));

            $user = User::whereRaw("LOWER(email) = '".$login."' OR LOWER(user_name) = '".$login."'")
                ->first();


            if ($user && md5($request->input('password')) ==  $user->password) {
                return $user;
            }

            return null;
        });

         /**
         * logout
         */
        $this->app->singleton(\Laravel\Fortify\Contracts\LogoutResponse::class,\App\Http\Responses\LogoutResponse::class);
    }
}
