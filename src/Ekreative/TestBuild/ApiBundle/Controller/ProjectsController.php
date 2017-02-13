<?php

namespace Ekreative\TestBuild\ApiBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProjectsController extends Controller
{
    /**
     * @Route("projects/{page}",defaults={"page" = "1"})
     * @Method("GET")
     * @ApiDoc(
     *   description="List of projects",
     *   section="Projects"
     * )

     */
    public function indexAction($page)
    {
        $data = $this->get('ekreative_redmine_login.client_provider')->get($this->getUser())->get('projects.json?page=' . $page)->getBody();
        $projects = json_decode($data, true);

        return new JsonResponse($projects);
    }
}
