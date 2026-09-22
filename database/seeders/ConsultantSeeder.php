<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ConsultantSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'consultant@mbsj.gov.my'],
            [
                'name' => 'Consultant MBSJ',
                'password' => Hash::make('password'),
                'role' => 'consultant',
                'unit_id' => null,
                'status' => 'active',
                'phone' => null,
            ]
        );

        $this->command?->info('Consultant seeded: consultant@mbsj.gov.my / password');
    }
}
