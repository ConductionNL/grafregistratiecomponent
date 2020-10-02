<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An GRC.
 *
 * @ApiResource(
 *     attributes={"pagination_items_per_page"=30},
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *     denormalizationContext={"groups"={"write"}, "enable_max_depth"=true},
 *     itemOperations={
 *          "get",
 *          "put",
 *          "delete",
 *          "get_change_logs"={
 *              "path"="/graves/{id}/change_log",
 *              "method"="get",
 *              "swagger_context" = {
 *                  "summary"="Changelogs",
 *                  "description"="Gets al the change logs for this resource"
 *              }
 *          },
 *          "get_audit_trail"={
 *              "path"="/graves/{id}/audit_trail",
 *              "method"="get",
 *              "swagger_context" = {
 *                  "summary"="Audittrail",
 *                  "description"="Gets the audit trail for this resource"
 *              }
 *          }
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\GraveRepository")
 * @Gedmo\Loggable(logEntryClass="Conduction\CommonGroundBundle\Entity\ChangeLog")
 *
 * @ApiFilter(BooleanFilter::class)
 * @ApiFilter(OrderFilter::class)
 * @ApiFilter(DateFilter::class, strategy=DateFilter::EXCLUDE_NULL)
 * @ApiFilter(SearchFilter::class)
 */
class Grave
{
    /**
     * @var UuidInterface The UUID identifier of this resource
     *
     * @example e2984465-190a-4562-829e-a8cca81aa35d
     *
     * @Assert\Uuid
     * @Groups({"read"})
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var Cemetery The grave in  which this burial has taken place
     *
     * @Groups({"read", "write"})
     * @MaxDepth(1)
     * @ORM\ManyToOne(targetEntity="App\Entity\Cemetery", inversedBy="graves")
     */
    private $cemetery;

    /**
     * @var string The reference of this Grave
     *
     * @example zb-01
     *
     * @Assert\Length(
     *     max = 255
     * )
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reference;

    /**
     * @var string A accomodation for this grave
     *
     * @example https://wrc.zaakonline.nl/organisations/16353702-4614-42ff-92af-7dd11c8eef9f
     *
     * @Gedmo\Versioned
     * @Assert\NotNull
     * @Assert\Url
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255)
     */
    private $accomodation;

    /**
     * @var string An person or organisation that holds the rights to this grave
     *
     * @example https://wrc.zaakonline.nl/organisations/16353702-4614-42ff-92af-7dd11c8eef9f
     *
     * @Gedmo\Versioned
     * @Assert\NotNull
     * @Assert\Url
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255)
     */
    private $owner;

    /**
     * @var array A list of persons or organisations that have a vested intresd in this grave
     *
     * @example
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="array")
     */
    private $interestedParties = [];

    /**
     * @var array A list of rulings concerning this grave
     *
     * @example
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="array")
     */
    private $rulings = [];

    /**
     * @var ArrayCollection The burials that have taken place in this grave
     *
     * @Groups({"read", "write"})
     * @MaxDepth(1)
     * @ORM\OneToMany(targetEntity="App\Entity\Burial", mappedBy="grave")
     */
    private $burials;

    /**
     * @var ArrayCollection The GraveCovers that are part of this Grave
     *
     * @Groups({"read", "write"})
     * @MaxDepth(1)
     * @ORM\ManyToMany(targetEntity="App\Entity\Cover", mappedBy="grave")
     */
    private $covers;

    /**
     * @var integer The maximum burials in a grave
     *
     * @example 3
     *
     * @Gedmo\Versioned
     * @Assert\NotNull
     * @Assert\Url
     * @Groups({"read", "write"})
     * @ORM\Column(type="integer", length=3)
     */
    private $capacity;

    /**
     * @var string The grave type of this Grave
     *
     * @example pdc/product
     * @Assert\Length(
     *     max = 255
     * )
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $graveType;

    /**
     * @var Datetime The moment this the rights on this grave expire
     *
     * @Groups({"read"})
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateRights;

    /**
     * @var Datetime The moment this entity was created
     *
     * @Groups({"read"})
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @var Datetime The moment this entity last Modified
     *
     * @Groups({"read"})
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateModified;

    public function __construct()
    {
        $this->burials = new ArrayCollection();
        $this->covers = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getAccomodation(): ?string
    {
        return $this->accomodation;
    }

    public function setAccomodation(string $accomodation): self
    {
        $this->accomodation = $accomodation;

        return $this;
    }

    public function getOwner(): ?string
    {
        return $this->owner;
    }

    public function setOwner(string $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getInterestedParties(): ?array
    {
        return $this->interestedParties;
    }

    public function setInterestedParties(array $interestedParties): self
    {
        $this->interestedParties = $interestedParties;

        return $this;
    }

    public function getRulings(): ?array
    {
        return $this->rulings;
    }

    public function setRulings(array $rulings): self
    {
        $this->rulings = $rulings;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getGraveType(): ?string
    {
        return $this->graveType;
    }

    public function setGraveType(?string $graveType): self
    {
        $this->graveType = $graveType;

        return $this;
    }

    public function getDateRights(): ?\DateTimeInterface
    {
        return $this->dateRights;
    }

    public function setDateRights(?\DateTimeInterface $dateRights): self
    {
        $this->dateRights = $dateRights;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(?\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateModified(): ?\DateTimeInterface
    {
        return $this->dateModified;
    }

    public function setDateModified(?\DateTimeInterface $dateModified): self
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    public function getCemetery(): ?Cemetery
    {
        return $this->cemetery;
    }

    public function setCemetery(?Cemetery $cemetery): self
    {
        $this->cemetery = $cemetery;

        return $this;
    }

    /**
     * @return Collection|Burial[]
     */
    public function getBurials(): Collection
    {
        return $this->burials;
    }

    public function addBurial(Burial $burial): self
    {
        if (!$this->burials->contains($burial)) {
            $this->burials[] = $burial;
            $burial->setGrave($this);
        }

        return $this;
    }

    public function removeBurial(Burial $burial): self
    {
        if ($this->burials->contains($burial)) {
            $this->burials->removeElement($burial);
            // set the owning side to null (unless already changed)
            if ($burial->getGrave() === $this) {
                $burial->setGrave(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Cover[]
     */
    public function getCovers(): Collection
    {
        return $this->covers;
    }

    public function addCover(Cover $cover): self
    {
        if (!$this->covers->contains($cover)) {
            $this->covers[] = $cover;
            $cover->addGrave($this);
        }

        return $this;
    }

    public function removeCover(Cover $cover): self
    {
        if ($this->covers->contains($cover)) {
            $this->covers->removeElement($cover);
            $cover->removeGrave($this);
        }

        return $this;
    }


}
