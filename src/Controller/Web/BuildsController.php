<?php

namespace App\Controller\Web;

use App\AWS\S3;
use App\Entity\App;
use App\Form\AppType;
use App\Roles\EkreativeUserRoles;
use App\Services\AppDataManager;
use App\Services\BuildsUploader;
use Ekreative\RedmineLoginBundle\Client\ClientProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/web/builds")
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
     * @Route("/show/{projectSlug}/{type}/{page}", name="project_builds", requirements={"type"="^ios|android$", "page": "\d+"}, defaults={"page": 1})
     * @param Request $request
     * @param $projectSlug
     * @param $type
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function index(Request $request, $projectSlug, $type, $page)
    {
        list($projectId, $upload, $delete, $projectName) = $this->getProjectIdAndPermissions($projectSlug);

        /** @var App $app */
        $app = new App();
        $app
            ->setType($type)
            ->setProjectId($projectId)
            ->setName($projectName)
            ->setType($type);

        if ($upload) {
            $form = $this->createForm(AppType::class, $app);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->buildUploader->upload($app->getBuild(), $app->getComment(), $app->getProjectId(), $app->getType());

                $this->addFlash('success', 'Build was downloaded');

                return $this->redirectToRoute('project_builds', ['projectSlug' => $projectSlug, 'type' => $app->getType()]);
            }
        }

        return $this->render('Builds/index.html.twig', [
            'slug' => $projectSlug,
            'delete' => $delete,
            'form' => isset($form) ? $form->createView() : null,
            'buildApp' => $app,
            'paginator' => $this->appDataManager->getAppsForProject($projectId, $type, $page)
        ]);
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

    /**
     * @Route("/install/{token}", name="build_install")
     */
    public function install($token)
    {
        try {
            /** @var App $app */
            $app = $this->appDataManager->getAppByToken($token);
            if (!$app) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            throw new NotFoundHttpException('No build with that token');
        }

        return $this->renderApp($app);
    }

    /**
     * @Route("/install/{platform}/{token}", name="build_install_platform")
     */
    public function installPlatform($token)
    {
        try {
            /** @var App $app */
            $app = $this->appDataManager->getAppByToken($token);
            if (!$app) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            throw new NotFoundHttpException('No build with that token');
        }

        return $this->renderApp($app);
    }

    /**
     * @Route("/installByCommit/{commit}/{jobName}", name="build_commit", requirements={"commit"="^[0-9a-f]{40}$", "jobName"="^[0-9a-z-_]+$"}, defaults={"jobName"=null})
     */
    public function commit($commit, $jobName = null)
    {
        try {
            /** @var App $app */
            $app = $this->appDataManager->getAppByCommit($commit, $jobName);

            if (!$app) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            throw new NotFoundHttpException('No build with that commit');
        }


        return $this->renderApp($app);
    }

    /**
     * @Route("/latest/{projectSlug}/{type}/{ref}/{jobName}", name="build_latest", requirements={"type"="^ios|android$", "ref"="^[0-9a-z-]+$", "jobName"="^[0-9a-z-_]+$"}, defaults={"jobName"=null})
     */
    public function latest($projectSlug, $type, $ref, $jobName = null)
    {
        list($projectId, $upload, $delete, $projectName) = $this->getProjectIdAndPermissions($projectSlug);

        try {
            /** @var App $app */
            $app = $this->appDataManager->getAppForProject($projectId, $type, $ref, $jobName);

            if (!$app) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            throw new NotFoundHttpException('No build with that parameters');
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
        ], UrlGeneratorInterface::ABSOLUTE_URL);
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
     * @Route("/release/{token}", name="build_inverse_release", methods={"GET"})
     */
    public function release($token)
    {
        try {
            /** @var App $app */
            $app = $this->appDataManager->getAppByToken($token);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('No build with that token');
        }

        $app->inverseRelease();

        try {
            $this->appDataManager->saveJsonData($app);
        } catch (\Exception $e) {
            throw new HttpException($e->getCode(), $e->getMessage());
        }

        return $this->redirectToRoute('project_builds', ['type' => $app->getType(), 'projectSlug' => $app->getProjectId()]);
    }

    /**
     * @Route("/delete/{project}/{type}/{token}", name="build_delete", methods={"POST"})
     */
    public function delete($project, $type, $token)
    {
        try {
            /** @var App $app */
            $app = $this->appDataManager->getAppByToken($token);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('No build with that token');
        }

        $this->s3Service->delete($app->getFilename());

        if ($app->isType(App::TYPE_IOS)) {
            $this->s3Service->delete($app->getPlistName());
        }

        $this->appDataManager->deleteJsonFiles($app);

        return $this->redirect($this->generateUrl('project_builds', ['type' => $type, 'projectSlug' => $project]));
    }

    /**
     * @Route("/upload/{project}/{type}", name="upload", methods={"POST"})
     * @throws \Exception
     */
    public function upload(Request $request, $project, $type)
    {
        $app = new App();
        $form = $this->createForm(AppType::class, $app);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            /** @var App $app */
            $app = $this->buildUploader->upload($app->getBuild(), $app->getComment(), $project, $type);
        }

        return $this->redirect($this->generateUrl('project_builds', ['type' => $app->getType(), 'projectSlug' => $app->getProjectId()]));
    }
}
