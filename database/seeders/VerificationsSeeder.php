<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VerificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('verifications')->insert([[
            'id' => Str::uuid()->toString(),
            'stream_id' => '2c49c07d-c776-41e0-ac70-5df05c70bc31',
            'verification_1' => 'Dept/Sub. Dept',
            'verification_2' => 'Section Head',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ],]);
    }
}
