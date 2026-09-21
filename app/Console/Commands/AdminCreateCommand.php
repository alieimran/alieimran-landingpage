<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminCreateCommand extends Command
{
    protected $signature = 'admin:create';

    protected $description = 'Create the Super Admin account (V1 supports a single administrator)';

    public function handle(): int
    {
        if (User::where('is_admin', true)->exists()) {
            $this->error('A Super Admin already exists. This application supports only one Super Admin in V1.');

            return self::FAILURE;
        }

        $name = $this->ask('Name');
        $email = $this->ask('Email address');
        $password = $this->secret('Password');
        $passwordConfirmation = $this->secret('Confirm password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'min:12'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $admin = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $admin->forceFill(['is_admin' => true])->save();

        $this->info("Super Admin created: {$admin->email}");

        return self::SUCCESS;
    }
}
