<?php

namespace App\Entity\Country;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Country.
 *
 * @ORM\Table(name="country")
 * @ORM\Entity(repositoryClass="App\Repository\Country\CountryRepository")
 */
class Country
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="iso_code", type="string", length=2, nullable=false, unique=true)
     *
     * @Assert\Length(
     *     min=1,
     *     max=2,
     *     minMessage="Country iso must be at least {{ limit }} characters long",
     *     maxMessage="Country iso cannot be longer than {{ limit }} characters",
     *     allowEmptyString=false
     * )
     */
    protected $isoCode;

    /**
     * @var float|null
     *
     * @ORM\Column(name="vat_rate", type="decimal", precision=5, scale=4, nullable=true)
     */
    protected $vatRate;

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return Country
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIsoCode(): ?string
    {
        return $this->isoCode;
    }

    /**
     * @return Country
     */
    public function setIsoCode(string $isoCode): self
    {
        $this->isoCode = $isoCode;

        return $this;
    }

    public function getVatRate(): ?float
    {
        return $this->vatRate;
    }

    /**
     * @return Country
     */
    public function setVatRate(?float $vatRate): self
    {
        $this->vatRate = $vatRate;

        return $this;
    }
}
