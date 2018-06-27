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
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Space", cascade={"persist"})
     */
    private $space;

    /**
     * @var
     */
    private $username;

    /**
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * @var string
     */
    private $salt;

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
     * @ORM\Column(type="datetime")
     */
    private $dateInscription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pathImg;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Share", mappedBy="user")
     */
    private $shares;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;


    /**
     * @param Share $share
     * @return $this
     */
    public function addShare(\App\Entity\Share $share)
    {
        $this->shares[] = $share;
        $share->setFile($this);
        return $this;
    }

    /**
     * @param Share $share
     */
    public function removeShare(\App\Entity\Share $share)
    {
        $this->shares->removeElement($share);
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getShare()
    {
        return $this->shares;
    }

    /**
     * @return array
     */
    public function getRoles() {
        return $this->roles;
    }

    /**
     * @return null|string
     */
    public function getSalt() {
        return $this->salt;
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     *
     */
    public function eraseCredentials() {}

    /**
     * User constructor.
     */
    public function __construct() {
        $this->roles = array("ROLE_USER");
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->shares = new \Doctrine\Common\Collections\ArrayCollection();

    }

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    /**
     * @param string $pseudo
     * @return User
     */
    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPathImg()
    {
        return $this->pathImg;
    }

    /**
     * @param mixed $pathImg
     */
    public function setPathImg($pathImg): void
    {
        $this->pathImg = $pathImg;
    }

    /**
     * @return mixed
     */
    public function getSpace()
    {
        return $this->space;
    }

    /**
     * @param mixed $space
     */
    public function setSpace($space): void
    {
        $this->space = $space;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active): void
    {
        $this->active = $active;
    }





}
