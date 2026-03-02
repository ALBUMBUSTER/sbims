<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AssignRoleIdsToUsers extends Seeder
{
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            // Assuming the old 'role' column still has string values
            // You'll need to temporarily keep the old column during migration
            if ($user->role) { // Old column
                $role = Role::where('name', $user->role)->first();
                if ($role) {
                    $user->role_id = $role->id;
                    $user->save();
                }
            }
        }
    }
}
