<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class Transition
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\Column(type="string", length=255) */
    private $name;

    /** @ORM\Column(type="simple_array", nullable=true) */
    private $froms;

    /** @ORM\Column(type="simple_array", nullable=true) */
    private $tos;

    public function __construct($name, $froms, $tos)
    {
        $this->name = $name;
        $this->froms = $froms;
        $this->tos = $tos;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getFroms()
    {
        return $this->froms;
    }

    /**
     * @param mixed $froms
     */
    public function setFroms($froms): void
    {
        $this->froms = $froms;
    }

    /**
     * @return mixed
     */
    public function getTos()
    {
        return $this->tos;
    }

    /**
     * @param mixed $tos
     */
    public function setTos($tos): void
    {
        $this->tos = $tos;
    }
}
