<?php

namespace Ekreative\TestBuild\ApiBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestCase extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->setUpClient();
    }

    protected function setUpClient()
    {
        $this->client = static::createClient([], ['HTTPS' => true]);
        $this->client->followRedirects(false);
    }

    protected function setUpFixtures($dir, $class, $function)
    {
        $file = $this->getPathFileFixture($dir, $class, $function);
        $this->client->getContainer()->get('ekreative_test_build_api.fixtures')->load($file);
        // Reboot the container so that listeners are reset
        $this->setUpClient();
    }

    /**
     * @return string
     */
    private function getPathFileFixture($dir, $class, $function)
    {
        $pathClassArr = explode('\\', $class);
        $className = $pathClassArr[count($pathClassArr) - 1];
        $fixturesDir = $dir . '/' . $className;

        return $fixturesDir . DIRECTORY_SEPARATOR . $function . '.yml';
    }
}
