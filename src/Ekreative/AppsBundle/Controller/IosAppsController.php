<?php

namespace Ekreative\AppsBundle\Controller;

use Ekreative\AppsBundle\Entity\IosApp;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IosAppsController extends Controller {

//    var $appsFolder = null;
//
//    public function __construct() {
//        $this->appsFolder = str_replace('/src/Ekreative/AppsBundle/Controller', '', __DIR__) . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'apps';
//        if (!is_dir($this->appsFolder)) {
//            mkdir($this->appsFolder);
//        }
//    }


    public function indexAction($id) {



        $folder = $this->getDoctrine()->getRepository('EkreativeAppsBundle:IosFolder')->find($id);
        if ($folder) {

            $em = $this->getDoctrine()->getManager();



            $app = new IosApp();
            $app->setFolder($folder);
            $form = $this->newAppForm($app);




            return $this->render('EkreativeAppsBundle:IosApps:index.html.twig', array(
                        'folder' => $folder,
                        'appform' => $form->createView(),
                            )
            );
        }

        throw new NotFoundHttpException("Page not found");
    }

    public function newAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();

        $folder = $this->getDoctrine()->getRepository('EkreativeAppsBundle:IosFolder')->find($id);
        $folderId = $folder->getId();
        $app = new IosApp();
        $app->setFolder($folder);
        $app->setDate(new \DateTime());


        $form = $this->newAppForm($app);

        $form->handleRequest($request);
//        $app->updateFile();
        $em->persist($app);
        $em->flush();
//        $app->updateFile();
//        $em->flush();




        $url = $this->generateUrl('ekreative_install_ios_app', array('folder' => $folderId, 'id' => $app->getId(), 'token' => $app->getToken()), true);

        $qrcode = 'http://chart.apis.google.com/chart?chl=' . urlencode($url) . '&chs=200x200&choe=UTF-8&cht=qr&chld=L%7C2';
        $app->setQrcode($qrcode);

        $em->flush();

        $em->persist($app);
        $em->flush();
        return new RedirectResponse($this->generateUrl('ekreative_ios_folder', array('id' => $folderId)));
    }

    public function deleleAction(Request $request, $id) {

        $app = $this->getDoctrine()->getRepository('EkreativeAppsBundle:IosApp')->find($id);


        if ($app) {
            $filderId = $app->getFolder()->getId();
            $em = $this->getDoctrine()->getManager();
            $em->remove($app);
            $em->flush();
//            unlink($this->appsFolder . DIRECTORY_SEPARATOR . $filderId . DIRECTORY_SEPARATOR . $id . '.apk');
            return new RedirectResponse($this->generateUrl('ekreative_ios_folder', array('id' => $filderId)));
        }
        throw new NotFoundHttpException("Page not found");
    }

    public function downloadAction(Request $request, $folder, $id, $token) {

        $app = $this->getDoctrine()->getRepository('EkreativeAppsBundle:IosApp')->find($id);
        
        if ($app && $app->getToken() == $token) {


            return $this->render('EkreativeAppsBundle:IosApps:download.html.twig', array(
                    'app' => $app,
                )
            );
        }
        throw new NotFoundHttpException("Page not found");
    }

    public function installAction(Request $request, $folder, $id, $token) {

        $app = $this->getDoctrine()->getRepository('EkreativeAppsBundle:IosApp')->find($id);

        if ($app && $app->getToken() == $token) {


            $urlPlist = 'itms-services:///?action=download-manifest&url=' . urlencode($app->getWebAbsolutePathPlist());
            return $this->render('EkreativeAppsBundle:IosApps:install.html.twig', array(
                    'urlPlist' => $urlPlist,
                    'app' => $app,
                )
            );
        }
        throw new NotFoundHttpException("Page not found");
    }

    private function newAppForm($entity) {
        return $this->createFormBuilder($entity)
                        ->add('uploadedFile', 'file', array('attr' => array('placeholder' => 'version', 'class' => "form-control")))
                        ->add('version', 'text', array('required' => true, 'attr' => array('placeholder' => 'version', 'class' => "form-control")))
                        ->add('comment', 'text', array('required' => false, 'attr' => array('placeholder' => 'comment', 'class' => "form-control")))
                        ->add('bundleIdentifier', 'text', array('required' => true, 'attr' => array('placeholder' => 'bundleIdentifier', 'class' => "form-control")))
                        ->setAction($this->generateUrl('ekreative_new_ios_app', array('id' => $entity->getFolder()->getId())))
                        ->setMethod('POST')
                        ->add('save', 'submit')
                        ->getForm();
    }

}
