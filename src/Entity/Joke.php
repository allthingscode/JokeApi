<?php

namespace App\Entity;

//use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="jokes")
 */
class Joke
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $joke;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $joke
     */
    public function setJoke(string $joke)
    {
        $this->joke = $joke;
    }

    /**
     * @return string
     */
    public function getJoke(): string
    {
        return $this->joke;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'joke' => $this->joke
        ];
    }
}