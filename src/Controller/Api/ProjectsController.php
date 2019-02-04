<?php

namespace App\Controller\Api;

use Ekreative\RedmineLoginBundle\Client\ClientProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ProjectsController extends AbstractController
{
    /**
     * @var ClientProvider
     */
    private $loginProvider;

    /**
     * ProjectsController constructor.
     * @param ClientProvider $loginProvider
     */
    public function __construct(ClientProvider $loginProvider)
    {
        $this->loginProvider = $loginProvider;
    }

    /**
     * @Route("projects/{page}",defaults={"page" = "1"}, methods={"GET"})
     */
    public function index($page)
    {
        $data = $this->loginProvider->get($this->getUser())->get('projects.json?page=' . $page)->getBody();
        $projects = json_decode($data, true);

        return $this->json($projects);
    }
}
