<?php

namespace App\Validator\IsUserExistByEmail;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsUserExistByEmail extends Constraint
{
    public $message = 'User with "{{ email }}" email is not exist.';

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy()
    {
        return \get_class($this).'Validator';
    }
}
