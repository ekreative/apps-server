<?php

namespace App\Controller\Api;

use App\Form\Model\ApiForm;
use App\Form\Model\ApiFormType;
use App\Services\AppDataManager;
use App\Services\BuildsUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/builds")
 */
class BuildsController extends AbstractController
{
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
     * @param BuildsUploader $buildUploader
     * @param AppDataManager $appDataManager
     */
    public function __construct(BuildsUploader $buildUploader, AppDataManager $appDataManager)
    {
        $this->buildUploader = $buildUploader;
        $this->appDataManager = $appDataManager;
    }

    /**
     * @Route("/{project}/{type}", methods={"GET"}, name="jenkins_projects")
     */
    public function builds(Request $request, $project, $type)
    {
        $apps = $this->appDataManager->getAppsForProject($project, $type, $request->query->get('page', 1));

        return $this->json($apps);
    }

    /**
     * @Route("/upload/{project}/{type}", name="jenkins_url", methods={"POST"})
     */
    public function upload(Request $request, $project, $type)
    {
        $apiForm = new ApiForm();
        $form = $this->createForm(ApiFormType::class, $apiForm);
        $form->handleRequest($request);
        $apiForm->setJobName($request->request->get('job-name'));

        try {
            $app = $this->buildUploader->upload(
                $apiForm->getApp(),
                $apiForm->getComment(),
                $project,
                $type,
                $apiForm->getRef(),
                $apiForm->getCommit(),
                $apiForm->getJobName(),
                $apiForm->isCi());

            $this->appDataManager->saveJsonData($app);
        } catch (\Exception $e) {
            return $this->json(
                ['error' =>
                    [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage()
                    ]
                ], Response::HTTP_BAD_REQUEST);
        }

        $data = $app->jsonSerialize();
        $data['install'] = $this->generateUrl('build_install', ['token' => $app->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->json($data);
    }
}
