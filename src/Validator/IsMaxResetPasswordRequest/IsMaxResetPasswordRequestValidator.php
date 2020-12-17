<?php

namespace App\Validator\IsMaxResetPasswordRequest;

use App\Component\HttpFoundation\Security\ResetEmailRequest;
use App\Repository\EmailResetTokenRepository;
use App\Repository\PersonRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsMaxResetPasswordRequestValidator extends ConstraintValidator
{
    /**
     * @var EmailResetTokenRepository
     */
    private $emailResetTokenRepository;

    /**
     * @var PersonRepository
     */
    private $personRepository;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(
        EmailResetTokenRepository $emailResetTokenRepository,
        ParameterBagInterface $parameterBag,
        PersonRepository $personRepository
    )
    {
        $this->emailResetTokenRepository = $emailResetTokenRepository;
        $this->parameterBag              = $parameterBag;
        $this->personRepository          = $personRepository;
    }

    /**
     * Check maximum requests
     */
    public function validate($instance, Constraint $constraint)
    {

        if (!$constraint instanceof IsMaxResetPasswordRequest) {
            throw new UnexpectedTypeException($constraint, IsMaxResetPasswordRequest::class);
        }

        if (!$instance instanceof ResetEmailRequest) {
            throw new UnexpectedTypeException($constraint, ResetEmailRequest::class);
        }

        $person = $this->personRepository->getPersonByEmail($instance->getEmail());

        if (!$person) {
            return false;
        }

        $personCount = $this->emailResetTokenRepository
            ->getCountPersonEmailTokens($person, new \DateTime());

        if ($personCount >= $this->parameterBag->get('reset_password')['max_reset_request']) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ hours }}', $this->parameterBag->get('reset_password')['expired_hours'])
                ->setParameter('{{ max }}', $this->parameterBag->get('reset_password')['max_reset_request'])
                ->addViolation();
        }
    }
}
