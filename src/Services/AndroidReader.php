<?php

namespace App\Services;

use ApkParser\Parser;
use App\AWS\S3;
use App\Entity\App;
use App\Factory\ReaderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AndroidReader implements ReaderInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var S3
     */
    private $s3;

    /**
     * IosReader constructor.
     * @param LoggerInterface $logger
     * @param S3 $s3
     */
    public function __construct(LoggerInterface $logger, S3 $s3)
    {
        $this->logger = $logger;
        $this->s3 = $s3;
    }


    /**
     * @param App $app
     * @return App
     * @throws \Exception
     */
    public function readData(App $app)
    {
        $build = $app->getBuild();

        $app->setType(App::TYPE_ANDROID);
        $this->readAndroidData($app, $build);

        $headers = [
            'ContentDisposition' => 'attachment;filename="' . $app->getDownloadNameFilename() . '"',
            'ContentType' => 'application/vnd.android.package-archive'
        ];

        $app->setBuildUrl($this->s3->upload($build->getRealPath(), $app->getFilename(), $headers));
        unlink($build->getRealPath());

        return $app;
    }

    public function getExtension()
    {
        return 'apk';
    }

    /**
     * @param App $app
     * @param UploadedFile $build
     */
    private function readAndroidData(App $app, UploadedFile $build)
    {
        try {
            $apk = new Parser($build->getRealPath());
            $manifest = $apk->getManifest();

            try {
                $app->setBundleId($manifest->getPackageName());
            } catch (\Exception $e) {
                $this->logger->error('Coundnt read apk bundle id', [
                    'e' => $e,
                    'manifest' => $manifest
                ]);
            }
            try {
                $app->setVersion($manifest->getVersionName());
            } catch (\Exception $e) {
                $this->logger->error('Coundnt read apk version name', [
                    'e' => $e,
                    'manifest' => $manifest
                ]);
            }

            try {
                $app->setBuildNumber($manifest->getVersionCode());
            } catch (\Exception $e) {
                $this->logger->error('Coundnt read apk version code', [
                    'e' => $e,
                    'manifest' => $manifest
                ]);
            }
            try {
                $app->setMinSdkLevel($manifest->getMinSdkLevel());
            } catch (\Exception $e) {
                $this->logger->error('Coundnt read apk min sdk', [
                    'e' => $e,
                    'manifest' => $manifest
                ]);
            }
            try {
                $app->setDebuggable($manifest->isDebuggable());
            } catch (\Exception $e) {
                $this->logger->error('Coundnt read apk debug', [
                    'e' => $e,
                    'manifest' => $manifest
                ]);
            }
            $app->setPermssions(implode(', ', array_keys($manifest->getPermissions())));

            $resourceId = $apk->getManifest()->getApplication()->getIcon();
            $resources = $apk->getResources($resourceId);
            $tmpfname = tempnam('/tmp', $manifest->getPackageName());
            file_put_contents($tmpfname, stream_get_contents($apk->getStream(end($resources))));
            try {
                $app->setIconUrl($this->s3->upload($tmpfname, $app->getIconFileName(), BuildsUploader::ICON_HEADERS));
            } catch (\Exception $e) {
                $this->logger->warning('Failed to upload icon from apk', [
                    'e' => $e
                ]);
            }
            unlink($tmpfname);

            $app->setAppServer($manifest->getMetaData('APP_SERVER'));
        } catch (\Exception $e) {
            $this->logger->error('Coundnt read apk manifest', [
                'e' => $e
            ]);
        }
    }
}
