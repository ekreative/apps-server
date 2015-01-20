<?php

namespace Ekreative\AppsBundle\Controller;

use Ekreative\AppsBundle\Entity\AndroidFolder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Ekreative\AppsBundle\Entity\AndroidApp;

class AndroidController extends BaseController {

    public function indexAction(Request $request, $id) {

        $folder = $this->getDoctrine()->getRepository('EkreativeAppsBundle:AndroidFolder')->find($id);
        if ($folder) {

            $em = $this->getDoctrine()->getManager();

            $app = new AndroidApp();
            $app->setFolder($folder);
            $form = $this->newAppForm($app);
            
            $folderType = $this->getCurrentFolderType();
            return $this->render('EkreativeAppsBundle:Android:appsList.html.twig', array(
                        'folder' => $folder,
                        'appform' => $form->createView(),
                        'currentFolderType' => BaseController::FOLDER_ANDROID,
                        'serveLink' => $this->serveLink($folderType)
                            )
            );
        }

        throw new NotFoundHttpException("Page not found");
    }

    public function newAction(Request $request, $folder) {

        $em = $this->getDoctrine()->getManager();

        $folder = $this->getDoctrine()->getRepository('EkreativeAppsBundle:AndroidFolder')->find($folder);
        $app = new AndroidApp();
        $app->setFolder($folder);
        $app->setDate(new \DateTime());

        $s3 = $this->container->getParameter('amazon_s3_base_url');

        $form = $this->newAppForm($app);

        $form->handleRequest($request);
        $em->persist($app);
        $em->flush();


//        
        $uploader = $this->getFileUploader();
        
        $headers = array(
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment;filename="' . $app->getFilename() . '"'
        );

        $url = $s3.'/'.$uploader->upload($app->getUploadedFile(),$app->getS3name(), $headers );
        
        $qrcode = 'http://chart.apis.google.com/chart?chl=' . urlencode($url) . '&chs=200x200&choe=UTF-8&cht=qr&chld=L%7C2';
        $app->setQrcode($qrcode);
        $em->persist($app);
        $em->flush();
        return new RedirectResponse($this->generateUrl('ekreative_folder_android_index', array('id' => $folder->getId())));
    }

    public function deleleAction(Request $request, $id) {

        $app = $this->getDoctrine()->getRepository('EkreativeAppsBundle:AndroidApp')->find($id);

        if ($app) {
            $folderId = $app->getFolder()->getId();
            $em = $this->getDoctrine()->getManager();
            
            $uploader = $this->getFileUploader();
            $uploader->delete($app->getS3name());
            
            $em->remove($app);
            $em->flush();
            return new RedirectResponse($this->generateUrl('ekreative_folder_android_index', array('id' => $folderId)));
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
                        ->setAction($this->generateUrl('ekreative_android_app_new', array('folder' => $entity->getFolder()->getId())))
                        ->setMethod('POST')
                        ->add('save', 'submit')
                        ->getForm();
    }

}
