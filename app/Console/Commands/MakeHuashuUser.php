<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MakeHuashuUser extends Command
{
    protected $signature = 'make:huashu-user
                            {--name=Huashu Admin : Display name for the user}
                            {--email=huashu@huashu.com : Login email}
                            {--password= : Password (auto-generated if omitted)}';

    protected $description = 'Create or reset a Huashu fulfillment portal user';

    public function handle(): int
    {
        $name     = $this->option('name');
        $email    = $this->option('email');
        $password = $this->option('password') ?: Str::random(16);

        /** @var User $user */
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'           => $name,
                'password'       => Hash::make($password),
                'role'           => 'huashu',
                'email_verified_at' => now(),
            ]
        );

        // Assign Spatie role (idempotent)
        if (! $user->hasRole('huashu')) {
            $user->assignRole('huashu');
        }

        $this->info('Huashu user ready.');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name',     $user->name],
                ['Email',    $user->email],
                ['Password', $password],
                ['URL',      url('/huashu/orders')],
            ]
        );

        $this->warn('Save the password now — it will not be shown again.');

        return self::SUCCESS;
    }
}
