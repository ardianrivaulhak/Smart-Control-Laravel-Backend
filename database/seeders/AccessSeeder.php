<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('accesses')->insert([
            [
                "id" => Str::uuid()->toString(),
                "name" => "Dashboard",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
            [
                "id" => Str::uuid()->toString(),
                "name" => "Approval",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
            [
                "id" => Str::uuid()->toString(),
                "name" => "Report",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
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
                "name" => "Master Data",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
            [
                "id" => Str::uuid()->toString(),
                "name" => "Stream",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
            [
                "id" => Str::uuid()->toString(),
                "name" => "Section",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
            [
                "id" => Str::uuid()->toString(),
                "name" => "Form",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
            [
                "id" => Str::uuid()->toString(),
                "name" => "Management",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
            [
                "id" => Str::uuid()->toString(),
                "name" => "Workflow",
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ],
        ]);
    }
}
