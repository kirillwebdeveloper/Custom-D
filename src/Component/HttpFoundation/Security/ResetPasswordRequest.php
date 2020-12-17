<?php

namespace App\Component\HttpFoundation\Security;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ResetPasswordRequest
 * @package App\Component\HttpFoundation\Security
 */
class ResetPasswordRequest
{
    /**
     * @Assert\NotBlank
     */
    private $password;

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
}
