<?php

namespace App\Entity\Agency;

use App\Entity\Customer\Offer;
use App\Entity\Property\Lot as PropertyLot;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="agency_mandate")
 * @ORM\Entity(repositoryClass="App\Repository\Agency\MandateRepository")
 */
class Mandate
{
    public const TYPE_SALE         = 'sale';
    public const TYPE_RENT         = 'rent';
    public const STATE_IN_PROGRESS = 'in_progress';
    public const STATE_CANCELLED   = 'cancelled';
    public const STATE_FINISHED    = 'finished';
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Agency\Agency", inversedBy="mandates")
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="company_id")
     * @Assert\NotBlank
     */
    private $agency;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Property\Lot")
     * @ORM\JoinColumn(name="property_lot_id", referencedColumnName="id")
     * @Assert\NotBlank
     */
    private $propertyLot;

    /**
     * @ORM\Column(name="type", type="string")
     */
    private $type;

    /**
     * @ORM\Column(name="exclusive", type="boolean")
     */
    private $exclusive;

    /**
     * @ORM\Column(name="commission_percent", type="float", nullable=true)
     */
    private $commissionPercent;

    /**
     * @ORM\Column(name="commission_amount", type="integer", nullable=true)
     */
    private $commissionAmount;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer\Offer", mappedBy="mandate")
     */
    private $offers;

    /**
     * @ORM\Column(name="state", type="string", options={"default": "in_progress"})
     */
    private $state = self::STATE_IN_PROGRESS;

    public function __construct()
    {
        $this->offers   = new ArrayCollection();
    }

    public function __toString(): ?string
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgency(): ?Agency
    {
        return $this->agency;
    }

    /**
     * @return Mandate
     */
    public function setAgency(Agency $agency): self
    {
        $this->agency = $agency;

        return $this;
    }

    public function getPropertyLot(): ?PropertyLot
    {
        return $this->propertyLot;
    }

    /**
     * @return Mandate
     */
    public function setPropertyLot(PropertyLot $propertyLot): self
    {
        $this->propertyLot = $propertyLot;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return Mandate
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getExclusive(): ?bool
    {
        return $this->exclusive;
    }

    /**
     * @return Mandate
     */
    public function setExclusive(bool $exclusive): self
    {
        $this->exclusive = $exclusive;

        return $this;
    }

    public function getCommissionPercent(): ?float
    {
        return $this->commissionPercent;
    }

    /**
     * @return Mandate
     */
    public function setCommissionPercent(?float $commissionPercent): self
    {
        $this->commissionPercent = $commissionPercent;

        return $this;
    }

    public function getCommissionAmount(): ?int
    {
        return $this->commissionAmount;
    }

    /**
     * @return Mandate
     */
    public function setCommissionAmount(?int $commissionAmount): self
    {
        $this->commissionAmount = $commissionAmount;

        return $this;
    }

    /**
     * @return Collection|Offer[]
     */
    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function addOffer(Offer $offer): self
    {
        if (!$this->offers->contains($offer)) {
            $this->offers[] = $offer;
            $offer->setMandate($this);
        }

        return $this;
    }

    public function removeOffer(Offer $offer): self
    {
        if ($this->offers->contains($offer)) {
            $this->offers->removeElement($offer);
            // set the owning side to null (unless already changed)
            if ($offer->getMandate() === $this) {
                $offer->setMandate(null);
            }
        }

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @return Mandate
     */
    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get the commission rate.
     *
     * @return float|int|null
     */
    public function getCommissionRate()
    {
        $value = $this->getCommissionPercent();

        return $value ? ($value / 100) : 0;
    }

    /**
     * Get the calculated commission.
     *
     * @param float|int $price
     *
     * @return float|int|null
     */
    public function getCalculatedCommission($price)
    {
        $total = $this->getCommissionAmount() ?: 0;

        return bcadd($total, $price * $this->getCommissionRate(), 2);
    }
}
