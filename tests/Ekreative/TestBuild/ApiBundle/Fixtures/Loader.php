<?php

namespace Ekreative\TestBuild\ApiBundle\Fixtures;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader as DoctrineLoader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

// extending WebTestCase is the easiest way to get hold of a kernel
class Loader extends WebTestCase
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var string
     */
    private $sqlitePath;

    /**
     * @var string
     */
    private $sqliteBackup;

    public function __construct(Registry $doctrine, $sqlitePath, $sqliteBackup)
    {
        $this->doctrine = $doctrine;
        $this->sqlitePath = $sqlitePath;
        $this->sqliteBackup = $sqliteBackup;
    }

    public static function clearBackup()
    {
        /** @var Loader $fixtures */
        $fixtures = static::createClient()->getContainer()->get('ekreative_test_build_api.fixtures');
        $fixtures->realClearBackup();
    }

    private function realClearBackup()
    {
        $fileSystem = new Filesystem();
        if ($fileSystem->exists($this->sqlitePath)) {
            $fileSystem->remove($this->sqlitePath);
        }
        if ($fileSystem->exists($this->sqliteBackup)) {
            $fileSystem->remove($this->sqliteBackup);
        }
    }

    /**
     * Caches the structure of the database to make loading faster.
     *
     * @param string $filePath
     */
    public function load($filePath)
    {
        $fileSystem = new Filesystem();
        if ($fileSystem->exists($this->sqlitePath)) {
            $fileSystem->remove($this->sqlitePath);
        }

        if ($fileSystem->exists($this->sqliteBackup)) {
            $fileSystem->copy($this->sqliteBackup, $this->sqlitePath, true);
            $this->doctrine->resetManager();
        } else {
            /** @var EntityManager $em */
            $em = $this->doctrine->getManager();

            $schemaTool = new SchemaTool($em);
            $schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());

            $em->getConnection()->close();
            $fileSystem->copy($this->sqlitePath, $this->sqliteBackup, true);
            $this->doctrine->resetManager();
        }

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        $loader = new DoctrineLoader();
        $loader->addFixture(new LoadData($filePath));

        $exectuor = new ORMExecutor($em);
        $exectuor->execute($loader->getFixtures(), true);

        $this->doctrine->getManager()->clear();

        $em->getConnection()->close();
        $this->doctrine->resetManager();
    }
}
