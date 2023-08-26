<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Permission;
use App\Models\StreamVerification;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // $this->call(UsersSeeder::class);
        // $this->call(SectionsSeeder::class);
        // $this->call(StreamsSeeder::class);
        // $this->call(DocumentsSeeder::class);
        // $this->call(RolesSeeder::class);
        // $this->call(AccessSeeder::class);
        // $this->call(PermissionsSeeder::class);

        // $this->call(LinesSeeder::class);
        // $this->call(FormsSeeder::class);
        // $this->call(StreamSectionHeadsSeeder::class);
        // $this->call(StreamVerificationsSeeder::class);

        // $this->call(StandardsSeeder::class);
        // $this->call(UserHasStreamsSeeder::class);
        $this->call(NotificationsSeeder::class);
    }
}
