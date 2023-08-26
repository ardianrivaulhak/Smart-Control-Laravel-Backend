<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LinesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('lines')->insert([
            [
                "id" => Str::uuid()->toString(),
                "section_id" => "1a1e5d58-1d6b-4795-a6a9-7bda863c0d9a",
                "name" => "Line 1",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null
            ], [
                "id" => Str::uuid()->toString(),
                "section_id" => "1a1e5d58-1d6b-4795-a6a9-7bda863c0d9a",
                "name" => "Line 2",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null
            ]
        ]);
    }
}
