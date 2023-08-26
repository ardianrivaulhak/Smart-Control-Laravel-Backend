<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        DB::table('sections')->insert([
            [
                "id" => Str::uuid()->toString(),
                "name" => "Section 1",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
            [
                "id" => Str::uuid()->toString(),
                "name" => "Section 2",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
        ]);
    }
}
