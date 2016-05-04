<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $seeds = [
            PermissionSeeder::class,
            UserSeeder::class,
            TagSeeder::class,
            DocumentSeeder::class,
            ClientSeeder::class,
            ResourceTypeSeeder::class,
            ResourceSeeder::class,
            DiscussionStatusSeeder::class,
            DiscussionSeeder::class,
            CommentSeeder::class,
            VoteSeeder::class,
        ];

        foreach ($seeds as $class) {
           	$this->call($class);
        }

        Model::reguard();
    }
}
