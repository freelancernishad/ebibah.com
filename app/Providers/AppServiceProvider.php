<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
            // Decrypt the password
            $decryptedPassword = decrypt(env('MAIL_PASSWORD'));

            // Set the mail configuration dynamically
            Config::set('mail.mailers.smtp.password', $decryptedPassword);

    }
}
