<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;



/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email", message="Il y a déjà un compte associé à cette adresse email")
 * @UniqueEntity("pseudo", message="Ce pseudo est déjà utilisé")
 */
class User implements UserInterface
{
    private $username;
    private $roles;
    private $salt;

    public function getRoles() {
        return $this->roles;
    }

    public function getSalt() {
        return $this->salt;
    }

    public function getUsername() {
        return $this->username;
    }

    public function eraseCredentials() {
        // Ici nous n'avons rien à effacer.
        // Cela aurait été le cas si nous avions un mot de passe en clair.
    }

    public function __construct() {
        // De base, on va attribuer au nouveau utilisateur, le rôle « ROLE_USER »
        $this->roles = array("ROLE_USER");
        // Chaque utilisateur va se voir attribuer une clé permettant
        // de saler son mot de passe. Cela n'est pas obligatoire,
        // on pourrait mettre $salt à null
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\Email(
     *     message = "L'email ('{{ value }}') est invalide.",
     *     checkMX = true
     * )
     * @ORM\Column(type="string", length=190, unique=true)
     *
     */
    private $email;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=190, unique=true)
     */
    private $pseudo;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=190)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=190, unique=true)
     */
    private $folder;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateInscription;

    /**
     * @return mixed
     */
    public function getDateInscription()
    {
        return $this->dateInscription;
    }

    /**
     * @param mixed $dateInscription
     */
    public function setDateInscription($dateInscription): void
    {
        $this->dateInscription = $dateInscription;
    }

    /**
     * @return mixed
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param mixed $folder
     */
    public function setFolder($folder): void
    {
        $this->folder = $folder;
    }

    public function getId()
    {
        return $this->id;
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

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

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
}
