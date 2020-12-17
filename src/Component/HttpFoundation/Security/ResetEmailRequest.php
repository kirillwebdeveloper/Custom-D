<?php

namespace App\Component\HttpFoundation\Security;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as AppAssert;

/**
 * Class ResetEmailRequest
 * @package App\Component\HttpFoundation\Security
 * @AppAssert\IsMaxResetPasswordRequest\IsMaxResetPasswordRequest
 */
class ResetEmailRequest
{
    /**
     * @Assert\NotBlank
     * @Assert\Email
     * @AppAssert\IsUserExistByEmail\IsUserExistByEmail
     */
    private $email;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
