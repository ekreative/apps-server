<?php

namespace Ekreative\TestBuild\ApiBundle\Controller;

use Ekreative\RedmineLoginBundle\Security\RedmineUser;
use Ekreative\TestBuild\ApiBundle\Form\AppType;
use Ekreative\TestBuild\CoreBundle\Entity\App;
use Mcfedr\JsonFormBundle\Controller\JsonController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/builds")
 * @Template()
 */
class BuildsController extends JsonController
{


    /**
     * @Route("/{project}/{type}")
     * @Method("GET")
     * @ApiDoc(
     *   description="Apps builds for project",
     *   section="Builds"
     * )
     */
    public function buildsAction($project, $type)
    {
        $apps = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppsForProject($project, $type);
        return new JsonResponse($apps);
    }


    /**
     * @Route("/upload/{project}/{type}", name="jenkins_url")
     * @Method("POST")
     * @ApiDoc(
     *   description="Post new build from jenkins",
     *   section="Builds",
     *   parameters={
     *      {"name"="comment", "dataType"="string", "required"=true, "description"="Comment for the build"},
     *      {"name"="app",  "dataType"="file", "required"=true, "description"="Build of the app"},
     *     {"name"="ci",  "dataType"="bool", "required"=false, "description"="if 'true' then the build is marked as a ci build"},
     *  }
     * )
     */
    public function uploadAction($project, $type)
    {


        $request = $this->getRequest();

        $buildsUploader = $this->get('ekreative_test_build_core.builds_uploader');
        $app = $buildsUploader->upload($request->files->get('app'), $request->request->get('comment'), $project, $type, $request->request->get('ci') == 'true');

        $data = $app->jsonSerialize();
        $data['install'] = $this->generateUrl('build_install', ['token' => $app->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($data);
    }


}


