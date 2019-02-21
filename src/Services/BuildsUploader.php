<?php

namespace App\Services;

use App\Entity\App;
use App\Factory\AppFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BuildsUploader
{
    const ICON_HEADERS = [
        'ContentDisposition' => 'filename="appicon.png"',
        'ContentType' => 'image/png'
    ];

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var AppDataManager
     */
    private $appDataManager;

    /**
     * @var AppFactory
     */
    private $appFactory;

    /**
     * BuildsUploader constructor.
     * @param UrlGeneratorInterface $router
     * @param AppDataManager $appDataManager
     * @param AppFactory $appFactory
     */
    public function __construct(UrlGeneratorInterface $router, AppDataManager $appDataManager, AppFactory $appFactory)
    {
        $this->router = $router;
        $this->appDataManager = $appDataManager;
        $this->appFactory = $appFactory;
    }

    /**
     * @param UploadedFile $file
     * @param $comment
     * @param $project
     * @param $type
     * @param null $ref
     * @param null $commit
     * @param null $jobName
     * @param bool $ci
     * @return App
     * @throws \Exception
     */
    public function upload(UploadedFile $file, $comment, $project, $type, $ref = null, $commit = null, $jobName = null, $ci = false)
    {

        $app = $this->appFactory->create($file, $comment, $project, $type, $ref, $commit, $jobName, $ci);

        $app->setQrcodeUrl('https://chart.apis.google.com/chart?chl=' . urlencode($this->router->generate('build_install_platform',
                ['token' => $app->getToken(), 'platform' => $app->getType()], UrlGeneratorInterface::ABSOLUTE_URL)) . '&chs=200x200&choe=UTF-8&cht=qr&chld=L%7C2');

        if ($app->getBuildUrl()) {
            $this->appDataManager->saveJsonData($app);
        } else {
            throw new \Exception('Build URL was not generated');
        }

        return $app;
    }
}
