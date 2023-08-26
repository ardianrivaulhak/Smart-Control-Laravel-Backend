<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SectionHasLinesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('section_has_lines')->insert([
            "id" => Str::uuid()->toString(),
            "section_id" => "2cf98cda-8d90-442d-843f-fd7600c9c20b",
            "line_id" => "e98202d7-487f-4e06-bd55-950f7ba7a4d1",
            "created_at" => now(),
            "updated_at" => now(),
            "deleted_at" => null,
        ]);
    }
}
