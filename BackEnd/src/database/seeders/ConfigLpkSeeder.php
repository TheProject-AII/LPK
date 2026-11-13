<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConfigLpkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('config_lpk')->insert([
            'id' => (string) Str::uuid(), // Menghasilkan UUID
            'url_android_app' => 'https://example.com/android-app',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
