<?php

namespace Database\Seeders;

use App\Models\ZohoConnectUser;
use Illuminate\Database\Seeder;

class ZohoConnectUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ZohoConnectUser::create([
            'email'     => 'admin@admin.com',
            'password'  => bcrypt('admin'),
        ]);
    }
}
