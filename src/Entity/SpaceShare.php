<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SpaceShareRepository")
 */
class SpaceShare
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $id_user_one;

    /**
     * @ORM\Column(type="integer")
     */
    private $id_user_two;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $life;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $password;

    private $userOne;

    private $userTwo;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getIdUserOne(): ?int
    {
        return $this->id_user_one;
    }

    /**
     * @param int $id_user_one
     * @return SpaceShare
     */
    public function setIdUserOne(int $id_user_one): self
    {
        $this->id_user_one = $id_user_one;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getIdUserTwo(): ?int
    {
        return $this->id_user_two;
    }

    /**
     * @param int $id_user_two
     * @return SpaceShare
     */
    public function setIdUserTwo(int $id_user_two): self
    {
        $this->id_user_two = $id_user_two;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLife(): ?\DateTimeInterface
    {
        return $this->life;
    }

    /**
     * @param \DateTimeInterface|null $life
     * @return SpaceShare
     */
    public function setLife(?\DateTimeInterface $life): self
    {
        $this->life = $life;

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
     * @param null|string $password
     * @return SpaceShare
     */
    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setUserOne($doctrine){
        $this->userOne = $doctrine
            ->getRepository(User::class)
            ->find($this->id_user_one);
    }

    public function setUserTwo($doctrine){
        $this->userTwo = $doctrine
            ->getRepository(User::class)
            ->find($this->id_user_two);
    }

    public function getUserOne(){
        return $this->userOne;
    }

    public function getUserTwo(){
        return $this->userTwo;
    }
}
