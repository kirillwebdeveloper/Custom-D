<?php

namespace App\Entity\Agency;

use App\Entity\Company\Company;
use App\Entity\Document\DocumentTypeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="agency")
 * @ORM\Entity(repositoryClass="App\Repository\Agency\AgencyRepository")
 */
class Agency implements DocumentTypeInterface
{
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="App\Entity\Company\Company", cascade={"persist"}, inversedBy="agency")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=false)
     */
    private $company;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Agency\Mandate", mappedBy="agency")
     */
    private $mandates;

    public function __construct()
    {
        $this->company  = new Company();
        $this->mandates = new ArrayCollection();
    }

    public function __toString(): ?string
    {
        return $this->company;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @return Agency
     */
    public function setCompany(Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection|Mandate[]
     */
    public function getMandates(): Collection
    {
        return $this->mandates;
    }

    public function addMandate(Mandate $mandate): self
    {
        if (!$this->mandates->contains($mandate)) {
            $this->mandates[] = $mandate;
            $mandate->setAgency($this);
        }

        return $this;
    }

    public function removeMandate(Mandate $mandate): self
    {
        if ($this->mandates->contains($mandate)) {
            $this->mandates->removeElement($mandate);
            // set the owning side to null (unless already changed)
            if ($mandate->getAgency() === $this) {
                $mandate->setAgency(null);
            }
        }

        return $this;
    }

    public static function getEntityName(): string
    {
        return 'Agency';
    }
}
