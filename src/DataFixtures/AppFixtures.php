<?php

namespace App\DataFixtures;

use App\Entity\League;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class AppFixtures extends Fixture implements FixtureInterface, ContainerAwareInterface
{
    protected $leagueList = [
        'Premier League',
        'EFL Championship'
    ];

    protected $teamList = [
        'Arsenal',
        'Chelsea',
        'Aston Villa',
        'Barnsley',
        'Birmingham City',
        'Blackburn Rovers',
        'Crystal Palace',
        'Everton',
        'Liverpool',
        'Manchester City'
    ];

    protected $stripColor = [
        '#ff6384',
        '#4bc0c0',
        '#4dc9f6',
        '#f67019',
        '#f53794',
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadUser($manager);
        $this->loadLeague($manager);
        $this->loadTeam($manager);
    }


    /**
     * load user
     * @param ObjectManager $manager
     */
    protected function loadUser(ObjectManager $manager)
    {
        $passwordEncoder = $this->container->get('security.password_encoder');

        $user = new User();
        $plainPassword = 'admin';

        $encoded = $passwordEncoder->encodePassword($user, $plainPassword);

        $user->setUsername('admin');
        $user->setPassword($encoded);
        $user->setRoles([User::ROLE_ADMIN]);

        $manager->persist($user);
        $manager->flush();
    }

    /**
     * load league
     * @param ObjectManager $manager
     */
    protected function loadLeague(ObjectManager $manager)
    {
        foreach ($this->leagueList as $key => $leagueName) {
            $league = new League();
            $league->setName($leagueName);

            $manager->persist($league);

            // save for teams
            $this->addReference("league{$key}", $league);
        }

        $manager->flush();
    }

    /**
     * load team
     * @param ObjectManager $manager
     */
    protected function loadTeam(ObjectManager $manager)
    {
        foreach ($this->teamList as $teamName) {

            /**
             * @var $league League
             */
            $league = $this->getReference('league' . rand(0, count($this->leagueList) - 1));

            $team = new Team();
            $team->setName($teamName);
            $team->setLeague($league);
            $team->setStrip($this->stripColor[rand(0, count($this->stripColor) - 1) % count($this->stripColor)]);

            $manager->persist($team);
        }

        $manager->flush();
    }
}
