<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\Module::create(['name' => 'Text Book','tag' => 'text_book']);
        // \App\Models\Module::create(['name' => 'Tuition','tag' => 'tuition']);
        // \App\Models\Module::create(['name' => 'Tuition Session','tag' => 'tuition_session']);
        // \App\Models\Module::create(['name' => 'Assignment','tag' => 'assignment']);
        // \App\Models\Module::create(['name' => 'Message','tag' => 'message']);
        // \App\Models\Module::create(['name' => 'Network','tag' => 'network']);
        // \App\Models\Module::create(['name' => 'Timeline','tag' => 'timeline']);
        // \App\Models\Module::create(['name' => 'Quiz','tag' => 'quiz']);
        // \App\Models\Module::create(['name' => 'Note Book','tag' => 'note_book']);
        // \App\Models\Module::create(['name' => 'Course','tag' => 'course']);
        // \App\Models\Module::create(['name' => 'Events','tag' => 'events']);
        // \App\Models\Module::create(['name' => 'Library','tag' => 'library']);
        // \App\Models\Module::create(['name' => 'Groups','tag' => 'groups']);
        // \App\Models\Module::create(['name' => 'Axis','tag' => 'axis']);
        // \App\Models\Module::create(['name' => 'Games','tag' => 'games']);
        // \App\Models\Module::create(['name' => 'Chat','tag' => 'chat']);
        // \App\Models\Module::create(['name' => 'Profile','tag' => 'profile']);
        // \App\Models\Module::create(['name' => 'To Do','tag' => 'to_do']);
        // \App\Models\Module::create(['name' => 'Post And Search Job','tag' => 'Post_and_search_job']);
        // \App\Models\Module::create(['name' => 'Search Course','tag' => 'search_course']);
        // \App\Models\Module::create(['name' => 'Find Tutor','tag' => 'find_tutor']);
        // \App\Models\Module::create(['name' => 'My Points','tag' => 'my_points']);
        // \App\Models\Module::create(['name' => 'Feedback','tag' => 'feedback']);
        // \App\Models\Module::create(['name' => 'Question And Answers','tag' => 'question_and_answers']);
        // \App\Models\Module::create(['name' => 'Guardian / Parents','tag' => 'guardian_parents']);
        // \App\Models\Module::create(['name' => 'Settings','tag' => 'settings']);
        // \App\Models\Module::create(['name' => 'Earnings / Payments','tag' => 'earnings_payments']);
        // \App\Models\Module::create(['name' => 'Access Rights','tag' => 'access_rights']);
        \App\Models\Module::create(['name' => 'User','tag' => 'user']);
        \App\Models\Module::create(['name' => 'Invite User','tag' => 'invite_user']);
        \App\Models\Module::create(['name' => 'Class Room','tag' => 'class_romm']);
        \App\Models\Module::create(['name' => 'School Sessions','tag' => 'school_sessions']);
        \App\Models\Module::create(['name' => 'School Diary','tag' => 'school_diary']);
    }
}
