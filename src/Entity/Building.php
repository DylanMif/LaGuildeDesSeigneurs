<?php

namespace App\Entity;

use App\Repository\BuildingRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BuildingRepository::class)]
class Building
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['building', 'character'])]
    private ?int $id = null;

    #[ORM\Column(length: 20, name: 'gls_name')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 20,
    )]
    #[Groups(['building'])]
    private ?string $name = null;

    #[ORM\Column(length: 20, name:'gls_slug')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 20,
    )]
    private ?string $slug = null;

    #[ORM\Column(length: 20, nullable: true, name:'gls_caste')]
    #[Assert\Length(
        min: 3,
        max: 20
    )]
    #[Groups(['building'])]
    private ?string $caste = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, name:'gls_strength')]
    #[Assert\PositiveOrZero]
    #[Groups(['building'])]
    private ?int $strength = null;

    #[ORM\Column(length: 50, nullable: true, name:'gls_image')]
    #[Assert\Length(
        min: 3,
        max: 50
    )]
    #[Groups(['building'])]
    private ?string $image = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, name:'gls_rating')]
    #[Assert\PositiveOrZero]
    #[Groups(['building'])]
    private ?int $rating = null;

    #[ORM\Column(length: 40, name:'gls_identifier')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 40,
        max: 40,
    )]
    #[Groups(['building', 'character'])]
    private ?string $identifier = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, name:'gls_creation')]
    #[Groups(['building'])]
    private ?\DateTimeInterface $creation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, name:'gls_modification')]
    #[Groups(['building'])]
    private ?\DateTimeInterface $modification = null;

    /**
     * @var Collection<int, Character>
     */
    #[ORM\OneToMany(targetEntity: Character::class, mappedBy: 'building')]
    #[Groups(['building'])]
    private Collection $characters;
    
    #[Groups(['building'])]
    private array $_links = [];

    public function __construct()
    {
        $this->characters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCaste(): ?string
    {
        return $this->caste;
    }

    public function setCaste(string $caste): static
    {
        $this->caste = $caste;

        return $this;
    }

    public function getStrength(): ?int
    {
        return $this->strength;
    }

    public function setStrength(int $strength): static
    {
        $this->strength = $strength;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getCreation(): ?\DateTimeInterface
    {
        return $this->creation;
    }

    public function setCreation(?\DateTimeInterface $creation): static
    {
        $this->creation = $creation;

        return $this;
    }

    public function getModification(): ?\DateTimeInterface
    {
        return $this->modification;
    }

    public function setModification(?\DateTimeInterface $modification): static
    {
        $this->modification = $modification;

        return $this;
    }

    /**
     * @return Collection<int, Character>
     */
    public function getCharacters(): Collection
    {
        return $this->characters;
    }

    public function addCharacter(Character $character): static
    {
        if (!$this->characters->contains($character)) {
            $this->characters->add($character);
            $character->setBuilding($this);
        }

        return $this;
    }

    public function removeCharacter(Character $character): static
    {
        if ($this->characters->removeElement($character)) {
            // set the owning side to null (unless already changed)
            if ($character->getBuilding() === $this) {
                $character->setBuilding(null);
            }
        }

        return $this;
    }

    public function getLinks(): array
    {
        return $this->_links;
    }

    public function setLinks(array $_links): static
    {
        $this->_links = $_links;

        return $this;
    }
}
