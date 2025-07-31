<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TasksTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('tasks')->delete();
        
        \DB::table('tasks')->insert(array (
            0 => 
            array (
                'id' => 18,
                'title' => '1',
                'reference' => '321d',
                'status' => 'in_progress',
                'started_at' => '2025-07-28 14:27:37',
                'completed_at' => NULL,
                'created_at' => '2025-07-28 14:27:24',
                'updated_at' => '2025-07-28 14:27:37',
                'user_id' => 1,
            ),
            1 => 
            array (
                'id' => 19,
                'title' => '2',
                'reference' => '321zdz',
                'status' => 'pending',
                'started_at' => NULL,
                'completed_at' => NULL,
                'created_at' => '2025-07-28 14:27:31',
                'updated_at' => '2025-07-28 14:27:31',
                'user_id' => 1,
            ),
            2 => 
            array (
                'id' => 20,
                'title' => '1',
                'reference' => '321edf',
                'status' => 'in_progress',
                'started_at' => '2025-07-28 14:29:25',
                'completed_at' => NULL,
                'created_at' => '2025-07-28 14:29:05',
                'updated_at' => '2025-07-28 14:29:25',
                'user_id' => 2,
            ),
            3 => 
            array (
                'id' => 22,
                'title' => '3',
                'reference' => '654654dde',
                'status' => 'done',
                'started_at' => NULL,
                'completed_at' => '2025-07-28 14:29:29',
                'created_at' => '2025-07-28 14:29:20',
                'updated_at' => '2025-07-28 14:29:29',
                'user_id' => 2,
            ),
        ));
        
        
    }
}