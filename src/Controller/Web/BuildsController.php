<?php

namespace App\Controller\Web;

use App\AWS\S3;
use App\Entity\App;
use App\Roles\EkreativeUserRoles;
use App\Services\AppDataManager;
use App\Services\BuildsUploader;
use Ekreative\RedmineLoginBundle\Client\ClientProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/builds")
 */
class BuildsController extends AbstractController
{
    /**
     * @var ClientProvider
     */
    private $loginProvider;

    /**
     * @var S3
     */
    private $s3Service;

    /**
     * @var BuildsUploader
     */
    private $buildUploader;

    /**
     * @var AppDataManager
     */
    private $appDataManager;

    /**
     * BuildsController constructor.
     * @param $loginProvider
     * @param S3 $s3Service
     * @param BuildsUploader $buildUploader
     * @param AppDataManager $appDataManager
     */
    public function __construct(ClientProvider $loginProvider, S3 $s3Service, BuildsUploader $buildUploader, AppDataManager $appDataManager)
    {
        $this->loginProvider = $loginProvider;
        $this->s3Service = $s3Service;
        $this->buildUploader = $buildUploader;
        $this->appDataManager = $appDataManager;
    }

    /**
     * @Route("/install/{token}", name="build_install")
     * @Route("/install/{platform}/{token}", name="build_install_platform")
     */
    public function install($token)
    {
        $app = $this->appDataManager->getAppByToken($token);
        if (!$app) {
            throw new NotFoundHttpException('No build with that token');
        }

        return $this->renderApp($app);
    }

    /**
     * @Route("/installByCommit/{commit}/{jobName}", requirements={"commit"="^[0-9a-f]{40}$", "jobName"="^[0-9a-z-_]+$"}, defaults={"jobName"=null})
     */
    public function commit($commit, $jobName = null)
    {
        $app = $this->appDataManager->getAppByCommit($commit, $jobName);
        if (!$app) {
            throw new NotFoundHttpException('No build with that commit');
        }

        return $this->renderApp($app);
    }

    /**
     * @Route("/latest/{projectSlug}/{type}/{ref}/{jobName}", requirements={"type"="^ios|android$", "ref"="^[0-9a-z-]+$", "jobName"="^[0-9a-z-_]+$"}, defaults={"jobName"=null})
     */
    public function latest($projectSlug, $type, $ref, $jobName = null)
    {
        list($projectId, $upload, $delete, $projectName) = $this->getProjectIdAndPermissions($projectSlug);

        $app = $this->appDataManager->getAppForProject($projectId, $type, $ref, $jobName);
        if (!$app) {
            throw new NotFoundHttpException('No build on that branch');
        }

        return $this->renderApp($app);
    }

    /**
     * @param App $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function renderApp(App $app)
    {
        if ($app->isType(App::TYPE_ANDROID)) {
            $url = $app->getBuildUrl();
        } elseif ($app->isType(App::TYPE_IOS)) {
            $url = 'itms-services:///?action=download-manifest&url=' . urlencode($app->getPlistUrl());
        }

        $install = $this->generateUrl('build_install_platform', [
            'token' => $app->getToken(),
            'platform' => $app->getType(),
        ], true);
        $qrcode = 'https://chart.apis.google.com/chart?chl=' . urlencode($install) . '&chs=200x200&choe=UTF-8&cht=qr&chld=L%7C2';

        return $this->render('Builds/install.html.twig', [
            'app' => $app,
            'url' => $url,
            'buildUrl' => $app->getBuildUrl(),
            'qrcode' => $qrcode,
            'install' => $install,
        ]);
    }

    /**
     * @Route("/release/{app}",name="build_inverse_release")
     * @throws \Exception
     */
    public function release(App $app)
    {
        $app->inverseRelease();

//        $em->persist($app);
//        $em->flush();
        $this->appDataManager->saveJsonData($app);

        return $this->redirect(
            $this->generateUrl('project_builds', ['type' => $app->getType(), 'projectSlug' => $app->getProjectId()])
        );
    }

    /**
     * @Route("/delete/{project}/{type}/{token}",name="build_delete", methods={"POST"})
     */
    public function delete($project, $type, $token)
    {
        /** @var App $app */
        $app = $this->appDataManager->getAppByToken($token);

        $this->s3Service->delete($app->getFilename());

        if ($app->isType(App::TYPE_ANDROID)) {
            $this->s3Service->delete($app->getPlistName());
        }

//        $em->remove($app);
//        $em->flush();
        $this->s3Service->delete($app->getJsonName());

        return $this->redirect($this->generateUrl('project_builds', ['type' => $type, 'projectSlug' => $project]));
    }

    /**
     * @Route("/upload/{project}/{type}",name="upload", methods={"POST"})
     * @throws \Exception
     */
    public function upload($project, $type, Request $request)
    {
        $form = $request->request->get('form');
        $files = $request->files->get('form');

        /** @var App $app */
        $app = $this->buildUploader->upload($files['build'], $form['comment'], $project, $type);

        return $this->redirect($this->generateUrl('project_builds', ['type' => $app->getType(), 'projectSlug' => $app->getProjectId()]));
    }

    private function newAppForm(App $app)
    {
        $form = $this->createFormBuilder($app)
            ->add('build', FileType::class, ['attr' => ['placeholder' => 'version', 'class' => 'form-control']])
            ->add('comment', TextType::class, ['required' => false, 'attr' => ['placeholder' => 'comment', 'class' => 'form-control']])
            ->setAction($this->generateUrl('upload', ['type' => $app->getType(), 'project' => $app->getProjectId()]))
            ->setMethod(Request::METHOD_POST)
            ->add('save', SubmitType::class, ['label' => 'Upload']);

        return $form->getForm();
    }

    /**
     * This has been moved to the end because the route conflicts with the others.
     *
     * @Route("/show/{projectSlug}/{type}/", name="project_builds", requirements={"type"="^ios|android$"})
     */
    public function index($projectSlug, $type)
    {
        list($projectId, $upload, $delete, $projectName) = $this->getProjectIdAndPermissions($projectSlug);

        $result = [];

        if ($upload) {
            $app = new App();
            $app->setType($type);
            $app->setProjectId($projectId);
            $form = $this->newAppForm($app);
            $result['appform'] = $form->createView();
        }

        $result['title'] = $projectName;

        $result['type'] = $type;
        $result['delete'] = $delete;
        $result['project'] = $projectId;

        $result['apps'] = $this->appDataManager->getAppsForProject($projectId, $type);

        return $this->render('Builds/index.html.twig', $result);
    }

    /**
     * @param $projectSlug
     * @return array
     */
    private function getProjectIdAndPermissions($projectSlug)
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw new AccessDeniedHttpException('You must be logged in to access this resource');
        }
        $client = $this->loginProvider->get($currentUser);

        $data = $client->get('projects/' . $projectSlug . '/memberships.json', [
            'query' => [
                'limit' => 100
            ]
        ])->getBody();
        $members = json_decode($data, true);

        $upload = false;
        $delete = false;

        $projectName = null;
        $projectId = null;

        foreach (array_key_exists('memberships', $members) ? $members['memberships'] : [] as $member) {
            $projectName = $member['project']['name'];
            $projectId = $member['project']['id'];

            $user = array_key_exists('user', $member) ? $member['user'] : ['id' => null];
            if ($user['id'] == $currentUser->getId()) {
                foreach ($member['roles'] as $role) {
                    if ($role['name'] == EkreativeUserRoles::ROLE_MANAGER) {
                        $delete = true;
                        $upload = true;
                    }
                    if ($role['name'] == EkreativeUserRoles::ROLE_DEVELOPER) {
                        $delete = true;
                        $upload = true;
                    }
                }
            }
        }

        if (!$projectId) {
            throw new NotFoundHttpException("Project with slug not found $projectSlug");
        }

        return [$projectId, $upload, $delete, $projectName];
    }
}
