<?php

namespace Ekreative\TestBuild\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/api/projects")
 * @Template()
 */

class ProjectsController extends Controller
{

    /**
     * @Route(".json")
     *      * @Method("GET")
     * @ApiDoc(
     *   description="Generate a sample of using the extension-api to generate this item",
     *   section="Source",
     *   filters={
     *     {"name"="ids", "dataType"="integer", "description"="If set, sets the ids on elements"}
     *   }
     * )

     */
    public function indexAction()
    {

        $data = $this->get('ekreative_redmine_login.client_provider')->get($this->getUser())->get('projects.json')->getBody();
        $projects = json_decode($data, true);
        return new JsonResponse($projects['projects']);
    }

}


