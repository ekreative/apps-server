<?php

namespace Ekreative\TestBuild\ApiBundle\Controller;

use Ekreative\RedmineLoginBundle\Security\RedmineUser;
use Ekreative\TestBuild\ApiBundle\Form\AppType;
use Ekreative\TestBuild\CoreBundle\Entity\App;
use Mcfedr\JsonFormBundle\Controller\JsonController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/builds")
 * @Template()
 */
class BuildsController extends JsonController
{



    /**
     * @Route("/{project}/{type}")
     * @Method("GET")
     * @ApiDoc(
     *   description="Apps builds for project",
     *   section="Builds"
     * )
     */
    public function buildsAction($project,$type)
    {
        $apps = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppsForProject($project, $type);
        return new JsonResponse(array('apps'=>$apps));
    }




    /**
     * @Route("/upload/{project}/{type}", name="jenkins_url")
     * @Method("POST")
     * @ApiDoc(
     *   description="Post new build from jenkins",
     *   section="Builds",
     *   parameters={
     *      {"name"="name", "dataType"="string", "required"=true, "description"="Name of app"},
     *      {"name"="app",  "dataType"="file", "required"=true, "description"="Build of the app"},
     *      {"name"="icon", "dataType"="file", "required"=true, "description"="app icon"}
     *  }
     * )
     */
    public function uploadAction($project, $type)
    {


        $request = $this->getRequest();

        /**
         * @var RedmineUser $user
         */

        $user = $this->getUser();
        $em   = $this->getDoctrine()->getManager();

        $app = new App();
        $app->setCreated(new \DateTime());

        $app->setProjectId($project);
        $app->setType($type);

        $appName = $request->request->get('name');
        $app->setVersion($request->request->get('version'));


        $app->setBuild($request->files->get('app'));
        $app->setIcon($request->files->get('icon'));
        $app->setBundleId('appBundle');

        $app->setCreatedName($user->getFirstName().'  '.$user->getLastName());
        $app->setCreatedId($user->getId());

        $icon  = $app->getIcon();
        $build = $app->getBuild();

        $app->setName($build->getClientOriginalName());

        if($appName){
            $app->setName($appName);
        }

        $em->persist($app);


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

        $s3Service = $this->get('ekreative_test_build_core.file_uploader');

        if ($icon) {
            $app->setIconUrl($s3Service->upload($icon->getRealPath(), $app->getIconFileName()));
            unlink($icon->getRealPath());
        }

        $app->setBuildUrl($s3Service->upload($build->getRealPath(), $app->getFilename(), $headers));
        unlink($build->getRealPath());


        if ($app->isType(App::TYPE_IOS)) {
            $tempFile = tempnam("/tmp", "plist");
            $plist    = $this->getDoctrine()
                             ->getRepository('EkreativeTestBuildCoreBundle:App')
                             ->getPlistString(
                                 $app->getBuildUrl(),
                                 $app->getBundleId(),
                                 $app->getVersion(),
                                 $build->getFilename());

            file_put_contents($tempFile, $plist);
            $app->setPlistUrl($s3Service->upload($tempFile, $app->getPlistName(), $headers));
            unlink($tempFile);
        }

        $app->setQrcodeUrl('http://chart.apis.google.com/chart?chl=' . urlencode($this->generateUrl('build_install',['token' => $app->getToken()])) . '&chs=200x200&choe=UTF-8&cht=qr&chld=L%7C2');
        $em->persist($app);
        $em->flush();
        return new JsonResponse($app);
    }


}


