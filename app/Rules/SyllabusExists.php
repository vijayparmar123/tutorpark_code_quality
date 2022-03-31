<?php

namespace App\Rules;

use App\Models\Syllabus;
use Illuminate\Contracts\Validation\Rule;

class SyllabusExists implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $syllabus = Syllabus::find(request('syllabus_id'));

        return $syllabus ? $syllabus->classes->contains($value) : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Class is not part of the provided syllabus.';
    }
}
