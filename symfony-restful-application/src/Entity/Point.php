<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="PointRepository")
 */
class Point
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $pointId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pointName;

    /**
     * @ORM\Column(type="string", length=4000, nullable=true)
     */
    private $pointDescription;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=8)
     */
    private $pointLongitude;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=8)
     */
    private $pointLatitude;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $pointCity;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PointType", inversedBy="points")
     * @ORM\JoinColumn(name="point_type_id", referencedColumnName="point_type_id")
     */
    private $pointType;

    public function getPointId(): ?int
    {
        return $this->pointId;
    }

    public function getPointName(): ?string
    {
        return $this->pointName;
    }

    public function setPointName(string $pointName): self
    {
        $this->pointName = $pointName;

        return $this;
    }

    public function getPointDescription(): ?string
    {
        return $this->pointDescription;
    }

    public function setPointDescription(?string $pointDescription): self
    {
        $this->pointDescription = $pointDescription;

        return $this;
    }

    public function getPointLongitude()
    {
        return $this->pointLongitude;
    }

    public function setPointLongitude($pointLongitude): self
    {
        $this->pointLongitude = $pointLongitude;

        return $this;
    }

    public function getPointLatitude()
    {
        return $this->pointLatitude;
    }

    public function setPointLatitude($pointLatitude): self
    {
        $this->pointLatitude = $pointLatitude;

        return $this;
    }

    public function getPointCity(): ?string
    {
        return $this->pointCity;
    }

    public function setPointCity(?string $pointCity): self
    {
        $this->pointCity = $pointCity;

        return $this;
    }

    public function getPointType(): ?PointType
    {
        return $this->pointType;
    }

    public function setPointType(?PointType $pointType): self
    {
        $this->pointType = $pointType;

        return $this;
    }
}
