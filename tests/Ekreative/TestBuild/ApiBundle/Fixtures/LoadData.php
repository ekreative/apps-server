<?php

namespace Ekreative\TestBuild\ApiBundle\Fixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;

class LoadData implements FixtureInterface
{
    /**
     * @var string
     */
    private $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function load(ObjectManager $manager)
    {
        $files[] = $this->filePath;
        Fixtures::load($files, $manager);
    }
}
