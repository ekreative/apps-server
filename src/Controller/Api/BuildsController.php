<?php

namespace App\Controller\Api;

use App\Services\AppDataManager;
use App\Services\BuildsUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/api/builds")
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
        $paginator = $this->appDataManager->getAppsForProject($project, $type, $request->query->get('page', 1));

        return $this->json($paginator->getData()->getValues());
    }

    /**
     * @Route("/upload/{project}/{type}", name="jenkins_url", methods={"POST"})
     */
    public function upload(Request $request, $project, $type)
    {
        try {
            $app = $this->buildUploader->upload(
                $request->files->get('app'),
                $request->request->get('comment'),
                $project,
                $type,
                $request->request->get('ref'),
                $request->request->get('commit'),
                $request->request->get('job-name'),
                $request->request->get('ci') == 'true'
                );
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
