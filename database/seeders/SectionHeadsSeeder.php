<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SectionHeadsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('section_heads')->insert([[
            'id' => Str::uuid()->toString(),
            'stream_id' => '2c49c07d-c776-41e0-ac70-5df05c70bc31',
            'name' => 'Casting',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ], [
            'id' => Str::uuid()->toString(),
            'stream_id' => 'bc9a78f8-7522-4eef-8093-e2735970a612',
            'name' => 'Buffing/Painting',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]]);
    }
}
