<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class QuestionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\QuestionType::create(['title' => 'comprehension', 'tag' => 'comprehension']);
        \App\Models\QuestionType::create(['title' => 'Q & A', 'tag' => 'q_a']);
        \App\Models\QuestionType::create(['title' => 'Multi Choice', 'tag' => 'mcq']);
        \App\Models\QuestionType::create(['title' => 'Fill the Blanks', 'tag' => 'blanks']);
        \App\Models\QuestionType::create(['title' => 'Match the Following', 'tag' => 'match_following']);
    }
}
