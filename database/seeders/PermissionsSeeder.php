<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('permissions')->insert([
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Create',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ], [
                'id' => Str::uuid()->toString(),
                'name' => 'Read',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Update',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Delete',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Export',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]
        ]);
    }
}
