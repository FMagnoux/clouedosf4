<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SpaceRepository")
 */
class Space
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\File", mappedBy="space", cascade={"persist"})
     */
    private $files;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Share", mappedBy="space")
     * @ORM\JoinColumn(nullable=false)
     */
    private $shares;

    /**
     * Space constructor.
     */
    public function __construct()
    {
        $this->shares = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * @return mixed
     */
    public function getShares()
    {
        return $this->shares;
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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Space
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return Space
     */
    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return Space
     */
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param File $file
     * @return $this
     */
    public function addFile(\App\Entity\File $file)
    {
        $this->files[] = $file;
        $file->setSpace($this);
        return $this;
    }

    /**
     * @param File $file
     */
    public function removeFile(\App\Entity\File $file)
    {
        $this->files->removeElement($file);
    }

    /**
     * @return mixed
     */
    public function getFiles()
    {
        return $this->files;
    }
}
