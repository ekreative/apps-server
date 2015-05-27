<?php

namespace Ekreative\AppsBundle\Controller;

use Ekreative\AppsBundle\Entity\AndroidApp;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AndroidController extends BaseController
{

    public function indexAction(Request $request, AndroidFolder $folder)
    {

        $app = new AndroidApp();
        $app->setFolder($folder);
        $form       = $this->newAppForm($app);
        $folderType = $this->getCurrentFolderType();

        return $this->render('EkreativeAppsBundle:Android:appsList.html.twig', array(
                'folder'            => $folder,
                'appform'           => $form->createView(),
                'currentFolderType' => BaseController::FOLDER_ANDROID,
                'serveLink'         => $this->serveLink($folderType)
            )
        );
    }

    public function newAction(Request $request, AndroidFolder $folder)
    {

        $em = $this->getDoctrine()->getManager();

        $app = new AndroidApp();
        $app->setFolder($folder);
        $app->setDate(new \DateTime());

        $s3   = $this->container->getParameter('amazon_s3_base_url');
        $form = $this->newAppForm($app);

        $form->handleRequest($request);
        $em->persist($app);
        $em->flush();

        $uploader = $this->getFileUploader();

        $headers = [
            'ContentDisposition' => 'attachment;filename="' . $app->getFilename() . '"',
            'ContentType'        => 'application/vnd.android.package-archive'
        ];

        $url = $s3 . '/' . $uploader->upload($app->getUploadedFile(), $app->getS3name(), $headers);

        $qrcode = 'http://chart.apis.google.com/chart?chl=' . urlencode($url) . '&chs=200x200&choe=UTF-8&cht=qr&chld=L%7C2';
        $app->setQrcode($qrcode);
        $app->setAlternativeComment($app->getUploadedFile()->getClientOriginalName());
        $em->persist($app);
        $em->flush();

        return new RedirectResponse($this->generateUrl('ekreative_folder_android_index', array('id' => $folder->getId())));
    }

    public function deleleAction(Request $request, AndroidApp $app)
    {

        $em       = $this->getDoctrine()->getManager();
        $folderId = $app->getFolder()->getId();

        $uploader = $this->getFileUploader();
        $uploader->delete($app->getS3name());

        $em->remove($app);
        $em->flush();

        return new RedirectResponse($this->generateUrl('ekreative_folder_android_index', array('id' => $folderId)));
    }

    public function installAction(AndroidApp $app)
    {

        $s3 = $this->container->getParameter('amazon_s3_base_url');

        return $this->render('EkreativeAppsBundle:Android:install.html.twig', array(
                'url' => $s3 . '/' . $app->getS3name(),
                'app' => $app
            )
        );
    }


    private function newAppForm($entity)
    {
        return $this->createFormBuilder($entity)
                    ->add('uploadedFile', 'file', array('attr' => array('placeholder' => 'version', 'class' => "form-control")))
                    ->add('version', 'text', array('required' => false, 'attr' => array('placeholder' => 'version', 'class' => "form-control")))
                    ->add('comment', 'text', array('required' => false, 'attr' => array('placeholder' => 'comment', 'class' => "form-control")))
                    ->setAction($this->generateUrl('ekreative_android_app_new', array('folder' => $entity->getFolder()->getId())))
                    ->setMethod('POST')
                    ->add('save', 'submit', ['label' => 'Upload'])
                    ->getForm();
    }

}
