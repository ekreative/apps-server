<?php

namespace Ekreative\AppsBundle\Controller;

use Ekreative\AppsBundle\Entity\AndroidApp;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AndroidAppsController extends Controller {

    var $appsFolder = null;

    public function __construct() {
        $this->appsFolder = str_replace('/src/Ekreative/AppsBundle/Controller', '', __DIR__) . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'apps';
        if (!is_dir($this->appsFolder)) {
            mkdir($this->appsFolder);
        }
    }

    public function indexAction($id) {



        $folder = $this->getDoctrine()->getRepository('EkreativeAppsBundle:AndroidFolder')->find($id);
        if ($folder) {

            $em = $this->getDoctrine()->getManager();



            $app = new AndroidApp();
            $app->setFolder($folder);
            $form = $this->newAppForm($app);




            return $this->render('EkreativeAppsBundle:AndroidApps:index.html.twig', array(
                        'folder' => $folder,
                        'appform' => $form->createView(),
                            )
            );
        }

        throw new NotFoundHttpException("Page not found");
    }

    public function newAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();

        $folder = $this->getDoctrine()->getRepository('EkreativeAppsBundle:AndroidFolder')->find($id);
        $folderId = $folder->getId();
        $app = new AndroidApp();
        $app->setFolder($folder);
        $app->setDate(new \DateTime());


        $form = $this->newAppForm($app);

        $form->handleRequest($request);
        $app->updateFile();
        $em->persist($app);
        $em->flush();
        $app->updateFile();
        $em->flush();




        $url = $this->generateUrl('ekreative_download_android_app', array('folder' => $folderId, 'id' => $app->getId()), true);

//        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/apps/' . $folderId . '/' . $app->getId() . '.apk';
        $qrcode = 'http://chart.apis.google.com/chart?chl=' . urlencode($url) . '&chs=200x200&choe=UTF-8&cht=qr&chld=L%7C2';
        $app->setQrcode($qrcode);

        $em->flush();

        $em->persist($app);
        $em->flush();
        return new RedirectResponse($this->generateUrl('ekreative_android_folder', array('id' => $folderId)));
    }

    public function deleleAction(Request $request, $id) {

        $app = $this->getDoctrine()->getRepository('EkreativeAppsBundle:AndroidApp')->find($id);


        if ($app) {
            $filderId = $app->getFolder()->getId();
            $em = $this->getDoctrine()->getManager();
            $em->remove($app);
            $em->flush();
            unlink($this->appsFolder . DIRECTORY_SEPARATOR . $filderId . DIRECTORY_SEPARATOR . $id . '.apk');
            return new RedirectResponse($this->generateUrl('ekreative_android_folder', array('id' => $filderId)));
        }
        throw new NotFoundHttpException("Page not found");
    }

    public function downloadAction(Request $request, $folder, $id) {

        $app = $this->getDoctrine()->getRepository('EkreativeAppsBundle:App')->find($id);
        
        if ($app) {
            $response = new \Symfony\Component\HttpFoundation\Response();
            $filename = $this->appsFolder . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $id . '.apk';
            // Set headers
            $response->headers->set('Cache-Control', 'no-cache');
            $response->headers->set('Content-type', 'application/vnd.android.package-archive');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $app->getFile() . '";');
            $response->headers->set('Content-length', filesize($filename));
            // Send headers before outputting anything
            $response->sendHeaders();
            return $response->setContent(readfile($filename));
        }
        throw new NotFoundHttpException("Page not found");
    }

    private function newAppForm($entity) {
        return $this->createFormBuilder($entity)
                        ->add('uploadedFile', 'file', array('attr' => array('placeholder' => 'version', 'class' => "form-control")))
                        ->add('version', 'text', array('required' => false, 'attr' => array('placeholder' => 'version', 'class' => "form-control")))
                        ->add('comment', 'text', array('required' => false, 'attr' => array('placeholder' => 'comment', 'class' => "form-control")))
                        ->setAction($this->generateUrl('ekreative_new_android_app', array('id' => $entity->getFolder()->getId())))
                        ->setMethod('POST')
                        ->add('save', 'submit')
                        ->getForm();
    }

}
