<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SetPlainPasswordForExistingUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set default password untuk user yang belum memiliki plain_password
        $users = User::whereNull('plain_password')->orWhere('plain_password', '')->get();
        
        foreach ($users as $user) {
            $defaultPassword = 'password123'; // Password default
            
            // Update plain_password dengan password default
            $user->update([
                'plain_password' => $defaultPassword,
                'password' => Hash::make($defaultPassword)
            ]);
            
            $this->command->info("Updated password for user: {$user->name} ({$user->email})");
        }
        
        $this->command->info("Total users updated: " . $users->count());
    }
}
