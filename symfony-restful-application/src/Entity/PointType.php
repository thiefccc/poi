<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PointTypeRepository")
 */
class PointType
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $pointTypeId;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $pointTypeName;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $pointTypeDescription;

    /**
     * @ORM\OneToMany(targetEntity="Point", mappedBy="pointType")
     * @Exclude
     */
    private $points;

    public function __construct()
    {
        $this->points = new ArrayCollection();
    }

    public function getPointTypeId(): ?int
    {
        return $this->pointTypeId;
    }

    public function getPointTypeName(): ?string
    {
        return $this->pointTypeName;
    }

    public function setPointTypeName(string $pointTypeName): self
    {
        $this->pointTypeName = $pointTypeName;

        return $this;
    }

    public function getPointTypeDescription(): ?string
    {
        return $this->pointTypeDescription;
    }

    public function setPointTypeDescription(?string $pointTypeDescription): self
    {
        $this->pointTypeDescription = $pointTypeDescription;

        return $this;
    }

    /**
     * @return Collection|Point[]
     */
    public function getPoints(): Collection
    {
        return $this->points;
    }

    public function addPoint(Point $point): self
    {
        if (!$this->points->contains($point)) {
            $this->points[] = $point;
            $point->setPointType($this);
        }

        return $this;
    }

    public function removePoint(Point $point): self
    {
        if ($this->points->contains($point)) {
            $this->points->removeElement($point);
            // set the owning side to null (unless already changed)
            if ($point->getPointType() === $this) {
                $point->setPointType(null);
            }
        }

        return $this;
    }
}
