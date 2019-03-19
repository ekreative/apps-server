<?php

namespace App\Controller\Web;

use App\Form\Model\SearchForm;
use App\Form\Model\SearchFormType;
use Ekreative\RedmineLoginBundle\Client\ClientProvider;
use Ekreative\RedmineLoginBundle\Form\Type\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * Class ProjectsController.
 */
class ProjectsController extends AbstractController
{
    /**
     * @var ClientProvider
     */
    private $loginProvider;

    /**
     * ProjectsController constructor.
     *
     * @param ClientProvider $loginProvider
     */
    public function __construct(ClientProvider $loginProvider)
    {
        $this->loginProvider = $loginProvider;
    }

    /**
     * @Route("{page}",name="projects", defaults={"page" = "1"}, requirements={"page": "\d+"})
     *
     * @param Request $request
     * @param $page
     *
     * @return Response
     */
    public function index(Request $request, $page)
    {
        $searchForm = new SearchForm();
        $form = $this->createForm(SearchFormType::class, $searchForm);
        $form->handleRequest($request);

        $searchForm->setPage($page);

        $data = $this->loginProvider->get($this->getUser())->get('projects.json?' . implode('&', $searchForm->getQueryArray()))->getBody();
        $projectsData = json_decode($data, true);

        $pages = ceil($projectsData['total_count'] / $projectsData['limit']);

        return $this->render('Projects/index.html.twig', [
            'searchForm' => $form->createView(),
            'pages' => $pages,
            'page' => $page,
            'projects' => $projectsData['projects'],
            'filter' => $searchForm
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
}
