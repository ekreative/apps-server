<?php

namespace App\Controller\Web;

use Ekreative\RedmineLoginBundle\Client\ClientProvider;
use Ekreative\RedmineLoginBundle\Form\Type\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * Class ProjectsController
 * @package App\Controller\Web
 *
 * @Route("")
 */
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
     * @Route("{page}",name="projects", defaults={"page" = "1"}, requirements={"page": "\d+"})
     */
    public function index($page, Request $request)
    {
        $query = [];

        $name = $request->query->get('q');
        if ($name) {
            $query[] = 'name=' . $name;
        }

        $query[] = 'page=' . $page;

        $data = $this->loginProvider->get($this->getUser())->get('projects.json?' . implode('&', $query))->getBody();
        $projectsData = json_decode($data, true);

        $pages = ceil($projectsData['total_count'] / $projectsData['limit']);

        return $this->render('Projects/index.html.twig' ,[
            'searchForm' => $this->getSearchForm($request)->createView(),
            'pages' => $pages,
            'page' => $page,
            'projects' => $projectsData['projects'],
            'q' => $name
        ]);
    }

    /**
     * @Route("/login", name="login", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function login(Request $request)
    {
        $session = $request->getSession();
        $form = $this->createForm(LoginType::class, [
            'username' => $session->get(Security::LAST_USERNAME)
                ], [
            'action' => $this->generateUrl('login_check')
        ]);
        $form->add('submit', SubmitType::class, ['label' => 'Sign In']);
        // get the login error if there is one
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                    Security::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        }

        return $this->render('Projects/login.html.twig', [
                    'last_username' => $session->get(Security::LAST_USERNAME),
                    'error' => $error,
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheck()
    {
    }

    private function getSearchForm(Request $request)
    {
        return $this->createFormBuilder(null, ['csrf_protection' => false])
                        ->add('q', TextType::class, ['required' => false, 'attr' => ['placeholder' => 'Search...'], 'data' => $request->query->get('q')])
                        ->setMethod(Request::METHOD_GET)
                        ->setAction($this->generateUrl('projects'))


                        ->getForm();
    }
}
