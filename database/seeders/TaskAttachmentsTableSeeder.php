<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TaskAttachmentsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('task_attachments')->delete();
        
        \DB::table('task_attachments')->insert(array (
            0 => 
            array (
                'id' => 2,
                'task_id' => 18,
                'filename' => 'attachments/TurHudyEWINmtW9L6we2AyioFB2GPz9Hg2Gm4Lm1.xlsx',
                'original_name' => 'CANEVAS-DUAC-1668067056.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'size' => 359379,
                'created_at' => '2025-07-28 15:06:31',
                'updated_at' => '2025-07-28 15:06:31',
                'user_id' => 1,
            ),
            1 => 
            array (
                'id' => 3,
                'task_id' => 22,
                'filename' => 'attachments/Z3asRdksDI8HvEZ30vACzYxwy9KyjFxF2aD8dRjk.pdf',
                'original_name' => 'File0008.PDF',
                'mime_type' => 'application/pdf',
                'size' => 11351409,
                'created_at' => '2025-07-29 11:54:33',
                'updated_at' => '2025-07-29 11:54:33',
                'user_id' => 2,
            ),
        ));
        
        
    }
}