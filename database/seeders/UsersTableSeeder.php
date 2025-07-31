<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'user2',
                'email' => 'user2@test.test',
                'email_verified_at' => NULL,
                'password' => '$2y$12$MwqXpXwqVL903APMdeAfPepLWEUL7nFqbOXcmb9WVCMkVEh898weO',
                'remember_token' => NULL,
                'created_at' => '2025-07-27 13:10:44',
                'updated_at' => '2025-07-27 13:10:44',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'user3',
                'email' => 'user3@test.test',
                'email_verified_at' => NULL,
                'password' => '$2y$12$ub.EcaCjlsUHoeqrpy7mWOzu38N43mCbnKa.e9ORFX7RAGWOsWKzC',
                'remember_token' => NULL,
                'created_at' => '2025-07-28 14:28:51',
                'updated_at' => '2025-07-28 14:28:51',
            ),
        ));
        
        
    }
}