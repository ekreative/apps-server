<?php

namespace Ekreative\TestBuild\WebBundle\Controller;

use Ekreative\TestBuild\CoreBundle\Entity\App;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Ekreative\RedmineLoginBundle\Form\LoginType;
use Symfony\Component\Security\Core\Security;


class ProjectsController extends Controller
{
    /**
     * @Route("{page}",name="projects", defaults={"page" = "1"}, requirements={"page": "\d+"})
     * @Template()
     */
    public function indexAction($page, Request $request)
    {


        $session = $request->getSession();
        $data = $this->get('ekreative_redmine_login.client_provider')->get($this->getUser())->get('projects.json?page='.$page)->getBody();
        $projectsData = json_decode($data, true);


        $projectsData['projects'];


        $session->set('projects',$projectsData['projects'] );
        $pages = round($projectsData['total_count']/$projectsData['limit']);

//        $projectIds = [];
//        $projects = [];
//        foreach($projectsData['projects'] as $project){
//            $projectIds[] = $project['id'];
//            $projects[$project['id']]=$project;
//        }
//
//
//
//        /**
//         * @var App[] $apps
//         */
//
//        $apps = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getLatestAppProject($projectIds);
//        foreach($apps as $app){
//            $projects[$app->getProjectId()]['apps'][]=$app;
//        }


        return [
            'pages'=>$pages,
            'page'=>$page,
            'projects'=>$projectsData['projects']
        ];
    }


    /**
     * @Route("/login", name="login")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return array
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();
        $form = $this->createForm(new LoginType(), [
            'username' => $session->get(Security::LAST_USERNAME)
        ], [
            'action' => $this->generateUrl('login_check')
        ]);
        $form->add('submit', 'submit', ['label' => 'Sign In']);
        // get the login error if there is one
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                Security::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        }
        return $this->render('@EkreativeTestBuildWeb/User/login.html.twig', [
            'last_username' => $session->get(Security::LAST_USERNAME),
            'error' => $error,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction()
    {

    }
}
