<?php

namespace Ekreative\TestBuild\WebBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;

class BuildsControllerTest extends TestCase
{
    public function testCommitAction()
    {
        $this->setUpFixtures(__DIR__, __CLASS__, __FUNCTION__);

        $this->client->request('GET', '/builds/installByCommit/197fd2c17325b8ffb3db56c787e80c580c47d4c5');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCommitAndJob()
    {
        $this->setUpFixtures(__DIR__, __CLASS__, __FUNCTION__);

        $this->client->request('GET', '/builds/installByCommit/197fd2c17325b8ffb3db56c787e80c580c47d4c5/a-job');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }
}
