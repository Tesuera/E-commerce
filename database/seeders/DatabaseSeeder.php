<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'zaw maung',
            'unique_id' => uniqid() . "_user_" . uniqid(),
            'email' => 'zawmmp.1319@gmail.com',
            'profile' => 'default.jpg',
            'role' => 'admin',
            'password' => Hash::make("asdffdsa")
        ]);
        User::create([
            'name' => 'eaint eaint',
            'unique_id' => uniqid() . "_user_" . uniqid(),
            'email' => 'eainteaint@gmail.com',
            'profile' => 'default.jpg',
            'role' => 'user',
            'password' => Hash::make("asdffdsa")
        ]);

        $categories = ['Sports', 'Edu', 'Food'];
        foreach($categories as $category) {
            Category::create([
                'title' => $category,
                'unique_id' => uniqid() . "_category_" . uniqid(),
                'user_id' => 1
            ]);
        }

        Product::factory(100)->create();
    }
}
