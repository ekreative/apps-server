<?php

/**
 * Created by PhpStorm.
 * User: vitaliy
 * Date: 8/6/15
 * Time: 1:03 AM
 */


namespace Ekreative\TestBuild\CoreBundle\Services;


use ApkParser\Parser;
use Doctrine\ORM\EntityManager;
use Ekreative\TestBuild\CoreBundle\AWS\S3;
use Ekreative\TestBuild\CoreBundle\Entity\App;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContext;

class BuildsUploader
{

    private $em;
    private $user;
    private $s3;
    private $router;
    private $ipaReader;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManager $em, SecurityContext $context, S3 $s3, IpaReader $ipaReader, UrlGeneratorInterface $router, LoggerInterface $logger)
    {
        $this->em        = $em;
        $this->s3        = $s3;
        $this->user      = $context->getToken()->getUser();
        $this->ipaReader = $ipaReader;
        $this->router    = $router;
        $this->logger = $logger;
    }

    public function upload(UploadedFile $file, $comment, $project, $type, $ci = false)
    {

        $s3Service = $this->s3;


        $app = new App();
        $app->setComment($comment);
        $app->setBuild($file);
        $app->setCreated(new \DateTime());

        $app->setProjectId($project);
        $app->setType($type);
        $app->setCi($ci);

        $build = $app->getBuild();
        $app->setRelease(false);
        $app->setDebuggable(false);
        $iconHeaders = [
            'ContentDisposition' => 'filename="appicon.png"',
            'ContentType'        => 'image/png'
        ];




        if ($app->isType(App::TYPE_IOS)) {

            $ipaReader = $this->ipaReader;

            $ipaReader->read($build->getRealPath());
            $app->setBundleName($ipaReader->getBundleName());
            $app->setVersion($ipaReader->getBundleVersion());
            $app->setMinimumOSVersion($ipaReader->getMinimumOSVersion());
            $app->setPlatformVersion($ipaReader->getPlatformVersion());
            $app->setBundleDisplayName($ipaReader->getBundleDisplayName());
            $app->setBuildNumber($ipaReader->getBundleShortVersionString());
            $app->setBundleSupportedPlatforms($ipaReader->getBundleShortVersionString());
            $app->setSupportedInterfaceOrientations($ipaReader->getSupportedInterfaceOrientations());
            $app->setBundleId($ipaReader->getBundleIdentifier());
            $unpackedIcon = $ipaReader->unpackImage($ipaReader->getIcon());

            $iconUrl      = $s3Service->upload($unpackedIcon, $app->getIconFileName(), $iconHeaders);
            $app->setIconUrl($iconUrl);


        } else {

            try {

                $apk      = new Parser($build->getRealPath());
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
                $resources  = $apk->getResources($resourceId);
                $tmpfname   = tempnam("/tmp", $manifest->getPackageName());
                file_put_contents($tmpfname, stream_get_contents($apk->getStream(end($resources))));
                $app->setIconUrl($s3Service->upload($tmpfname, $app->getIconFileName()), $iconHeaders);
                unlink($tmpfname);

            } catch (\Exception $e) {
                $this->logger->error('Coundnt read apk manifest', [
                    'e' => $e
                ]);
            }

        }

        $app->setCreatedName($this->user->getFirstName() . '  ' . $this->user->getLastName());
        $app->setCreatedId($this->user->getId());


        $app->setName($build->getClientOriginalName());
        $this->em->persist($app);


        if ($app->isType(App::TYPE_IOS)) {
            $headers = array(
                'ContentType'        => 'application/octet-stream',
                'ContentDisposition' => 'attachment;filename="' . $app->getDownloadNameFilename() . '"'
            );
        } else if ($app->isType(App::TYPE_ANDROID)) {
            $headers = [
                'ContentDisposition' => 'attachment;filename="' . $app->getDownloadNameFilename() . '"',
                'ContentType'        => 'application/vnd.android.package-archive'
            ];
        }

        $app->setBuildUrl($s3Service->upload($build->getRealPath(), $app->getFilename(), $headers));
        unlink($build->getRealPath());


        if ($app->isType(App::TYPE_IOS)) {
            $tempFile = tempnam("/tmp", "plist");

            $plist = $this->em->getRepository('EkreativeTestBuildCoreBundle:App')
                              ->getPlistString(
                                  $app->getBuildUrl(),
                                  $app->getBundleId(),
                                  $app->getVersion(),
                                  $build->getFilename());

            file_put_contents($tempFile, $plist);
            $app->setPlistUrl($s3Service->upload($tempFile, $app->getPlistName(), $headers));
            unlink($tempFile);
        }

        $app->setQrcodeUrl('http://chart.apis.google.com/chart?chl=' . urlencode($this->router->generate('build_install',
                ['token' => $app->getToken()])) . '&chs=200x200&choe=UTF-8&cht=qr&chld=L%7C2');
        $this->em->persist($app);
        $this->em->flush();

        return $app;
    }




}
