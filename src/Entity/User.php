<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email", message="L'email a déjà été utilisé")
 */
class User implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le prenom ne doit pas être vide")
     */
    private $first_name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le nom ne doit pas être vide")
     */
    private $last_name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="L'email ne doit pas être vide")
     * @Assert\Email(message="Le format du mail n'est pas valide")
*/
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le mot de passe n'a pas été rempli")
     * @Assert\Length(min="2", minMessage="Le mot de passe est trop court")
     */
    private $password;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Offer", inversedBy="users")
     */
    private $offers;

    public function __construct()
    {
        $this->offers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

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
        }

        return $this;
    }

    public function removeOffer(Offer $offer): self
    {
        if ($this->offers->contains($offer)) {
            $this->offers->removeElement($offer);
        }

        return $this;
    }

    public function jsonSerialize()
    {
        $offers = array();
        foreach ($this->offers as $offer) {
            $offers[] = $offer;
        }
        /** @var Offer $offer */
        return [
            "firstName" => $this->first_name,
            "lastName" => $this->last_name,
            "email" => $this->email,
            "offers" => $offers
        ];
    }
}
