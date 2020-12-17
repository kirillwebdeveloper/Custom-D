<?php

namespace App\Validator\IsUserExistByEmail;

use App\Repository\PersonRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsUserExistByEmailValidator extends ConstraintValidator
{
    /**
     * @var PersonRepository
     */
    private $personRepository;

    public function __construct(PersonRepository $personRepository)
    {
        $this->personRepository = $personRepository;
    }

    /**
     * Checks if person is exist by email.
     *
     * @param Constraint     $constraint     The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsUserExistByEmail) {
            throw new UnexpectedTypeException($constraint, IsUserExistByEmail::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $personCount = $this->personRepository
            ->getCountPersonsByEmail($value);

        if (!$personCount) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ email }}', $value)
                ->addViolation();
        }
    }
}
