<?php

namespace App\Controller\Web;

use App\AWS\S3;
use App\Entity\App;
use App\Form\AppType;
use App\Form\Model\BuildSearchForm;
use App\Form\Model\BuildSearchFormType;
use App\Services\AppDataManager;
use App\Services\BuildsUploader;
use Ekreative\RedmineLoginBundle\Client\ClientProvider;
use Endroid\QrCode\Response\QrCodeResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Endroid\QrCode\QrCode;

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
     *
     * @param $loginProvider
     * @param S3             $s3Service
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
     * @Route("/web/builds/show/{projectSlug}/{type}", name="project_builds", defaults={"type": null})
     *
     * @param Request $request
     * @param $projectSlug
     * @param $type
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function index(Request $request, $projectSlug, $type)
    {
        list($projectId, $projectName) = $this->getProjectIdAndPermissions($projectSlug);

        /** @var App $app */
        $app = new App();
        $app
            ->setType($type)
            ->setProjectId($projectId)
            ->setName($projectName)
            ->setType($type);

        $form = $this->createForm(AppType::class, $app);

        $form->handleRequest($request);

        $search = new BuildSearchForm();
        $search->setType($type);
        $searchForm = $this->createForm(BuildSearchFormType::class, $search);
        $searchForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $app = $this->buildUploader->upload($app->getBuild(), $app->getComment(), $app->getProjectId(), $app->getType());

            $this->addFlash('success', (strtoupper($app->getType()) . ' Build was downloaded'));

            return $this->redirectToRoute('project_builds', ['projectSlug' => $projectSlug]);
        }

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute('project_builds', ['projectSlug' => $projectSlug, 'type' => $search->getType()]);
        }

        return $this->render('builds/index.html.twig', [
            'slug' => $projectSlug,
            'form' => $form->createView(),
            'searchForm' => $searchForm->createView(),
            'buildApp' => $app,
            'paginator' => $this->appDataManager->getAppsForProject($projectId, $type, $request->query->get('page', 1))
        ]);
    }

    /**
     * @param $projectSlug
     *
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

        $projectName = null;
        $projectId = null;

        foreach (array_key_exists('memberships', $members) ? $members['memberships'] : [] as $member) {
            $projectName = $member['project']['name'];
            $projectId = $member['project']['id'];
        }

        if (!$projectId) {
            throw new NotFoundHttpException("Project with slug not found $projectSlug");
        }

        return [$projectId, $projectName];
    }

    /**
     * @Route("/web/builds/install/{token}", name="build_install")
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
     * @Route("/web/builds/install/{platform}/{token}", name="build_install_platform")
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
     * @Route("/web/builds/installByCommit/{commit}/{jobName}", name="build_commit", requirements={"commit"="^[0-9a-f]{40}$", "jobName"="^[0-9a-z-_]+$"}, defaults={"jobName"=null})
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
     * @Route("/web/builds/latest/{projectSlug}/{type}/{ref}/{jobName}", name="build_latest", requirements={"type"="^ios|android|exe$", "ref"="^[0-9a-z-]+$", "jobName"="^[0-9a-z-_]+$"}, defaults={"jobName"=null})
     */
    public function latest($projectSlug, $type, $ref, $jobName = null)
    {
        list($projectId, $projectName) = $this->getProjectIdAndPermissions($projectSlug);

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
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function renderApp(App $app)
    {
        if ($app->isType(App::TYPE_ANDROID) || $app->isType(App::TYPE_EXE)) {
            $url = $app->getBuildUrl();
        } elseif ($app->isType(App::TYPE_IOS)) {
            $url = 'itms-services:///?action=download-manifest&url=' . urlencode($app->getPlistUrl());
        }

        $install = $this->generateUrl('build_install_platform', [
            'token' => $app->getToken(),
            'platform' => $app->getType(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render('builds/install.html.twig', [
            'app' => $app,
            'url' => $url,
            'buildUrl' => $app->getBuildUrl(),
            'qrcode' => $this->generateUrl('build_install_qr', ['token' => $app->getToken()]),
            'install' => $install,
        ]);
    }

    /**
     * @Route("/web/builds/qr/{token}", name="build_install_qr")
     */
    public function appQr($token)
    {
        $install = $this->generateUrl('build_install', [
            'token' => $token,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $qr = new QrCode($install);
        $qr->setSize(150);
        return new QrCodeResponse($qr);
    }

    /**
     * @Route("/web/builds/release/{token}", name="build_inverse_release", methods={"GET"})
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
     * @Route("/web/builds/delete/{project}/{type}/{token}", name="build_delete", methods={"POST"})
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
     * @Route("/web/builds/upload/{project}/{type}", name="upload", methods={"POST"})
     *
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
