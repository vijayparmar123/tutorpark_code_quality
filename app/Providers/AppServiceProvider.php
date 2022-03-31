<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Builder::macro('whereSyllabus', function($syllabusId = null) {
            return ($syllabusId != null) ? $this->where('syllabus_id', $syllabusId) : $this;
        });

        Builder::macro('whereSyllabusIn', function($syllabusId = null) {
            return ($syllabusId != null) ? $this->whereIn('syllabus_id', [$syllabusId]) : $this;
        });
        
        Builder::macro('whereLevel', function($levelId = null) {
            return ($levelId != null) ? $this->where('level_id', $levelId) : $this;
        });

        Builder::macro('whereClassIn', function($class_id = null) {
            return ($class_id != null) ? $this->whereIn('class_ids', [$class_id]) : $this;
        });

        Builder::macro('whereClass', function($class_id = null) {
            return ($class_id != null) ? $this->where('class_id', $class_id) : $this;
        });

        Builder::macro('whereSubject', function($subject_id = null) {
            return ($subject_id != null) ? $this->where('subject_id', $subject_id) : $this;
        });

        Builder::macro('whereSubjectIn', function($subject_id = null) {
            return ($subject_id != null) ? $this->whereIn('subject_id', [$subject_id]) : $this;
        });

        // Builder::macro('whereTopic', function($topic_id = null) {
        //     return ($topic_id != null) ? $this->where('topic_id', $topic_id) : $this;
        // });

        // Builder::macro('whereTopicIn', function($topic_id = null) {
        //     return ($topic_id != null) ? $this->whereIn('topic_id', [$topic_id]) : $this;
        // });
    }
}
