<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        DB::table('users')->insert(
            [
                [
                    "id" => Str::uuid()->toString(),
                    "npk" => 123,
                    "role_id" => "f9dc56db-0b04-4b8b-ac88-9a2b084b265b",
                    "email" => "admin1@gmail.com",
                    "password" => bcrypt("admin123"),
                    "name" => "Admin",
                    "is_active" => 1,
                    "photo_url" => "test123.com",
                    "email_verified_at" => now(),
                    "deleted_at" => null,
                    "created_at" => now(),
                    "updated_at" => now(),
                ],
                [
                    "id" => Str::uuid()->toString(),
                    "npk" => 1234567,
                    "role_id" => "93fcaf46-a021-4e7a-af16-02c8b49d7d6f",
                    "email" => "superadmin@gmail.com",
                    "password" => bcrypt("superadmin"),
                    "name" => "Super Admin",
                    "is_active" => 1,
                    "photo_url" => "superadmin.com",
                    "email_verified_at" => now(),
                    "deleted_at" => null,
                    "created_at" => now(),
                    "updated_at" => now(),
                ]
            ]
        );
    }
}
