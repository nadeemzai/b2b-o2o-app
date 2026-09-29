<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
            'role'              => 'retailer',
            'is_active'         => true,
        ];
    }

    // ──────────────────────────────────────────────
    // States
    // ──────────────────────────────────────────────

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }

    public function retailer(): static
    {
        return $this->state(['role' => 'retailer']);
    }

    public function storeStaff(): static
    {
        return $this->state(['role' => 'store_staff']);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    // Convenience state: creates the user AND inserts the matching pivot row in user_roles.
    // Usage: User::factory()->withRole('retailer')->create()
    //        User::factory()->admin()->withRole('admin')->withRole('retailer')->create()  — chain for multi-role
    public function withRole(string $role): static
    {
        return $this->afterCreating(function (User $user) use ($role) {
            \App\Models\UserRole::firstOrCreate([
                'user_id' => $user->id,
                'role'    => $role,
            ]);
        });
    }
}
