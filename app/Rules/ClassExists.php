<?php

namespace App\Rules;

use App\Models\TpClass;
use Illuminate\Contracts\Validation\Rule;

class ClassExists implements Rule
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
        $class = TpClass::find(request('class_id'));

        return $class ? $class->subjects->contains($value) : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Subject is not part of the provided class.';
    }
}
