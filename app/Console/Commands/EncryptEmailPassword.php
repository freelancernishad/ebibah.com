<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class EncryptEmailPassword extends Command
{
    protected $signature = 'encrypt:email-password {password}';
    protected $description = 'Encrypt the email password and display it.';

    public function handle()
    {
        $password = $this->argument('password');
        $encryptedPassword = encrypt($password);
        $this->info('Encrypted Password: ' . $encryptedPassword);
    }
}
