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

class ProjectsController extends Controller {

    /**
     * @Route("{page}",name="projects", defaults={"page" = "1"}, requirements={"page": "\d+"})
     * @Template()
     */
    public function indexAction($page, Request $request) {


        $session = $request->getSession();
        $query = [];

        $name = $session->get('searchQery');
        if ($name) {
            $query[] = 'name=' . $name;
        }

        $query[] = 'page=' . $page;

        $data = $this->get('ekreative_redmine_login.client_provider')->get($this->getUser())->get('projects.json?' . implode('&', $query))->getBody();
        $projectsData = json_decode($data, true);

        $session->set('projects', $projectsData['projects']);
        $pages = ceil($projectsData['total_count'] / $projectsData['limit']);


        return [
            'searshForm' => $this->getSearshForm()->createView(),
            'pages' => $pages,
            'page' => $page,
            'projects' => $projectsData['projects']
        ];
    }

    /**
     * @Route("/login", name="login")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return array
     */
    public function loginAction(Request $request) {
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
    public function loginCheckAction() {
        
    }

    /**
     * @Route("/updateSearch", name="web_projects_updatesearch")
     */
    public function updateSearchAction(Request $request) {


        $form = $this->getSearshForm();
        $session = $this->getRequest()->getSession();


        if ($request->getMethod() == "POST") {
            $form->submit($request);
            if ($form->isValid()) {
                $postData = current($request->request->all());

                if ($form->get('reset')->isClicked()) {
                    $session->set('searchQery', null);
                } else {
                    $session->set('searchQery', $postData['qery']);
                }

                return $this->redirect($this->generateUrl('projects'));
            }
        }
    }

    private function getSearshForm() {

        $session = $this->getRequest()->getSession();
        $defaultData = array('qery' => $session->get('searchQery'));
        return $this->createFormBuilder($defaultData)
                        ->add('qery', 'text',['attr'=>['placeholder'=>'Search...']])
                        ->add('submit', 'submit', ['attr' => ['class' => 'btn btn-default'],'label'=>'Find!'])
                        ->add('reset' , 'submit', ['attr' => ['class' => 'btn btn-default']])
                        ->setMethod('post')
                        ->setAction($this->generateUrl('web_projects_updatesearch'))
                        ->getForm();
    }

}
