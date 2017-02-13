<?php

namespace Ekreative\TestBuild\WebBundle\Controller;

use Ekreative\RedmineLoginBundle\Form\LoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class ProjectsController extends Controller
{
    /**
     * @Route("{page}",name="projects", defaults={"page" = "1"}, requirements={"page": "\d+"})
     * @Template()
     */
    public function indexAction($page, Request $request)
    {
        $query = [];

        $name = $request->query->get('q');
        if ($name) {
            $query[] = 'name=' . $name;
        }

        $query[] = 'page=' . $page;

        $data = $this->get('ekreative_redmine_login.client_provider')->get($this->getUser())->get('projects.json?' . implode('&', $query))->getBody();
        $projectsData = json_decode($data, true);

        $pages = ceil($projectsData['total_count'] / $projectsData['limit']);

        return [
            'searshForm' => $this->getSearshForm($request)->createView(),
            'pages' => $pages,
            'page' => $page,
            'projects' => $projectsData['projects'],
            'q' => $name
        ];
    }

    /**
     * @Route("/login", name="login")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     *
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

    private function getSearshForm(Request $request)
    {
        return $this->get('form.factory')->createNamedBuilder(null, 'form', [
            'q' => $request->query->get('q')
        ], [
            'csrf_protection' => false
        ])
                        ->add('q', 'text', ['required' => false, 'attr' => ['placeholder' => 'Search...']])
                        ->setMethod('get')
                        ->setAction($this->generateUrl('projects'))

                        ->getForm();
    }
}
