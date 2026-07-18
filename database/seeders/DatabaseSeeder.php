<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['owner', 'admin', 'karyawan'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
