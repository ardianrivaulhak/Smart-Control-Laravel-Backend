<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('documents')->insert([
            [
                "id" => Str::uuid()->toString(),
                "name" => "Claim",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
            [
                "id" => Str::uuid()->toString(),
                "name" => "Procedure",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
            [
                "id" => Str::uuid()->toString(),
                "name" => "Pokayoke & TJDF",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
        ]);
    }
}
