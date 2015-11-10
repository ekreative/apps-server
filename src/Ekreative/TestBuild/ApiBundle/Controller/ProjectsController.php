<?php

namespace Ekreative\TestBuild\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class ProjectsController extends Controller
{

    /**
     * @Route("{page}",defaults={"page" = "1"})
     * @Method("GET")
     * @ApiDoc(
     *   description="List of projects",
     *   section="Projects"
     * )

     */
    public function indexAction($page)
    {

        $data = $this->get('ekreative_redmine_login.client_provider')->get($this->getUser())->get('projects.json?page='.$page)->getBody();
        $projects = json_decode($data, true);
        return new JsonResponse($projects['projects']);
    }

}


