<?php

declare(strict_types=1);

namespace App\Model\Input;

use Symfony\Component\Validator\Constraints as Assert;

class TeamInput implements InputModelInterface
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string|null
     *
     * @Assert\NotBlank()
     */
    private $leagueId;

    /**
     * @var string|null
     *
     * @Assert\NotBlank()
     */
    private $strip;

    /**
     * @return null|string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getLeagueId(): string
    {
        return $this->leagueId;
    }

    /**
     * @param null|string $leagueId
     */
    public function setLeagueId(string $leagueId)
    {
        $this->leagueId = $leagueId;
    }

    /**
     * @return null|string
     */
    public function getStrip(): string
    {
        return $this->strip;
    }

    /**
     * @param null|string $strip
     */
    public function setStrip(string $strip)
    {
        $this->strip = $strip;
    }
}
