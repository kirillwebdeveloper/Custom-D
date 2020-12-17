<?php

namespace App\Validator\IsMaxResetPasswordRequest;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsMaxResetPasswordRequest extends Constraint
{
    public $message = 'Cannot send request more than "{{ max }}" times. Try again after {{ hours }} hours.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return \get_class($this).'Validator';
    }
}
