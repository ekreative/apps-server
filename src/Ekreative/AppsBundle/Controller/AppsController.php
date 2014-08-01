<?php

namespace Ekreative\AppsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AppsController extends Controller {

    var $appsFolder = null;

    public function __construct() {
        $this->appsFolder = str_replace('/src/Ekreative/AppsBundle/Controller', '', __DIR__) . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'apps';
        if (!is_dir($this->appsFolder)) {
            mkdir($this->appsFolder);
        }
    }

    public function indexAction($id) {



        $folder = $this->getDoctrine()->getRepository('EkreativeAppsBundle:Folder')->find($id);
        if ($folder) {

            $em = $this->getDoctrine()->getManager();



            $app = new \Ekreative\AppsBundle\Entity\App();
            $app->setFolder($folder);
            $form = $this->newAppForm($app);
            return $this->render('EkreativeAppsBundle:Apps:index.html.twig', array(
                        'folder' => $folder,
                        'appform' => $form->createView(),
                            )
            );
        }

        return new \Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl('ekreative_apps_homepage'));
    }

    public function newAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();

        $folder = $this->getDoctrine()->getRepository('EkreativeAppsBundle:Folder')->find($id);
        $folderId = $folder->getId();
        $app = new \Ekreative\AppsBundle\Entity\App();
        $app->setFolder($folder);
        $app->setDate(new \DateTime());
        
        
        $form = $this->newAppForm($app);

        $form->handleRequest($request);
        $app->updateFile();
        $em->persist($app);
        $em->flush();
        $app->updateFile();
        $em->flush();

        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/apps/' . $folderId . '/' . $app->getId() . '.apk';
        $qrcode = 'http://chart.apis.google.com/chart?chl=' . urlencode($url) . '&chs=200x200&choe=UTF-8&cht=qr&chld=L%7C2';
        $app->setQrcode($qrcode);

        $em->flush();

        $em->persist($app);
        $em->flush();
        return new \Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl('ekreative_folder', array('id' => $folderId)));
    }

    public function deleleAction(Request $request, $id) {

        $app = $this->getDoctrine()->getRepository('EkreativeAppsBundle:App')->find($id);


        if ($app) {
            $filderId = $app->getFolder()->getId();
            $em = $this->getDoctrine()->getManager();
            $em->remove($app);
            $em->flush();
            unlink($this->appsFolder.DIRECTORY_SEPARATOR.$filderId.DIRECTORY_SEPARATOR.$id.'.apk');
            return new \Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl('ekreative_folder', array('id' => $filderId)));
        }
        return new \Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl('ekreative_folder', array('id' => $filderId)));
    }

    private function newAppForm($entity) {
        return $this->createFormBuilder($entity)
                        ->add('uploadedFile', 'file', array('attr'=>array('placeholder'=>'version','class'=>"form-control")))
                        ->add('version', 'text', array('required'=>false, 'attr'=>array('placeholder'=>'version','class'=>"form-control")))
                        ->add('comment', 'text', array('required'=>false, 'attr'=>array('placeholder'=>'comment','class'=>"form-control")))
                        ->setAction($this->generateUrl('ekreative_new_app',array('id'=>$entity->getFolder()->getId())))
                        ->setMethod('POST')
                        ->add('save', 'submit')
                        ->getForm();
    }

}
