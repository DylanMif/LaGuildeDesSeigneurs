<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[ORM\Table(name: '`character`')]
class Character
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['character', 'building'])]
    private ?int $id = 1;

    #[ORM\Column(length: 20, name:'gls_name')]
    #[Assert\NotNull] // Pour que ce ne soit pas null
    #[Assert\NotBlank] // Pour que ce ne soit pas blanc
    #[Assert\Length( //Définit une taille mini et maxi
        min: 3,
        max: 20
    )]
    #[Groups(['character'])]
    private ?string $name = null;

    #[ORM\Column(length: 50, name:'gls_surname')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 50
    )]
    #[Groups(['character'])]
    private ?string $surname = null;

    #[ORM\Column(length: 20, nullable: true, name:'gls_caste')]
    #[Assert\Length(
        min: 3,
        max: 20
    )]
    #[Groups(['character'])]
    private ?string $caste = null;

    #[ORM\Column(length: 20, name:'gls_knowledge')]
    #[Assert\Length(
        min: 3,
        max: 20
    )]
    #[Groups(['character'])]
    private ?string $knowledge = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, name:'gls_intelligence')]
    #[Assert\PositiveOrZero] // OU #[Assert\Positive] si on ne veut pas de 0
    #[Groups(['character'])]
    private ?int $intelligence = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, name:'gls_strength')]
    #[Assert\PositiveOrZero]
    #[Groups(['character'])]
    private ?int $strength = null;

    #[ORM\Column(length: 50, nullable: true, name:'gls_image')]
    #[Assert\Length(
        min: 3,
        max: 50
    )]
    #[Groups(['character'])]
    private ?string $image = null;

    #[ORM\Column(length: 20, name:'gls_slug')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 20
    )]
    #[Groups(['character'])]
    private ?string $slug = null;

    #[ORM\Column(length: 20, name:'gls_kind')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 20
    )]
    #[Groups(['character'])]
    private ?string $kind = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name:'gls_creation')]
    private ?\DateTimeInterface $creation = null;

    #[ORM\Column(length: 40)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 40, // si on veut une taille fixe il suffit
        max: 40, // de mettre la même valeur pour min et max
    )]
    #[Groups(['character', 'building'])]
    private ?string $identifier = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name:'gls_modification')]
    #[Groups(['character'])]
    private ?\DateTimeInterface $modification = null;

    #[ORM\ManyToOne(inversedBy: 'characters')]
    #[Groups(['character'])]
    private ?Building $building = null;

    #[Groups(['character'])]
    private array $_links = [];

    #[ORM\ManyToOne(inversedBy: 'characters')]
    #[Groups(['character'])]
    private ?User $user = null;

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

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    public function getCaste(): ?string
    {
        return $this->caste;
    }

    public function setCaste(?string $caste): static
    {
        $this->caste = $caste;

        return $this;
    }

    public function getKnowledge(): ?string
    {
        return $this->knowledge;
    }

    public function setKnowledge(string $knowledge): static
    {
        $this->knowledge = $knowledge;

        return $this;
    }

    public function getIntelligence(): ?int
    {
        return $this->intelligence;
    }

    public function setIntelligence(int $intelligence): static
    {
        $this->intelligence = $intelligence;

        return $this;
    }

    public function getStrength(): ?int
    {
        return $this->strength;
    }

    public function setStrength(?int $strength): static
    {
        $this->strength = $strength;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

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

    public function getKind(): ?string
    {
        return $this->kind;
    }

    public function setKind(string $kind): static
    {
        $this->kind = $kind;

        return $this;
    }

    public function getCreation(): ?\DateTimeInterface
    {
        return $this->creation;
    }

    public function setCreation(\DateTimeInterface $creation): static
    {
        $this->creation = $creation;

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

    public function getModification(): ?\DateTimeInterface
    {
        return $this->modification;
    }

    public function setModification(\DateTimeInterface $modification): static
    {
        $this->modification = $modification;

        return $this;
    }

    public function getBuilding(): ?Building
    {
        return $this->building;
    }

    public function setBuilding(?Building $building): static
    {
        $this->building = $building;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
