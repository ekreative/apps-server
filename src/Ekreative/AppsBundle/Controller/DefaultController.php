<?php

namespace Ekreative\AppsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller {

    var $appsFolder = null;

    public function __construct() {
        $this->appsFolder = str_replace('/src/Ekreative/AppsBundle/Controller', '', __DIR__) . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'apps';
        if (!is_dir($this->appsFolder)) {
            mkdir($this->appsFolder);
        }
    }

    public function indexAction() {

        $folder = new \Ekreative\AppsBundle\Entity\Folder();
        $folder->setDate(new \DateTime());

        $form = $this->newFolderForm($folder);
        $folders = $this->getDoctrine()->getRepository('EkreativeAppsBundle:Folder')->findAll();
        return $this->render('EkreativeAppsBundle:Default:index.html.twig', array(
                    'folders' => $folders,
                    'folderform' => $form->createView(),
                        )
        );
    }

    public function newfolderAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $folder = new \Ekreative\AppsBundle\Entity\Folder();
        $folder->setDate(new \DateTime());
        $form = $this->newFolderForm($folder);
        $form->handleRequest($request);
        $em->persist($folder);
        $em->flush();
        mkdir($this->appsFolder . DIRECTORY_SEPARATOR . $folder->getId());
        return new \Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl('ekreative_apps_homepage'));
    }

    public function deleleFolderAction(Request $request, $id) {

        $folder = $this->getDoctrine()->getRepository('EkreativeAppsBundle:Folder')->find($id);

        if ($folder) {
            $em = $this->getDoctrine()->getManager();
            if (count($folder->getApp()) == 0) {
                $em->remove($folder);
                $em->flush();
                rmdir($this->appsFolder . DIRECTORY_SEPARATOR . $id);
            }
        }

        return new \Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl('ekreative_apps_homepage'));
    }

    private function newFolderForm($entity) {
        return $this->createFormBuilder($entity)
                        ->add('name', 'text')
                        ->setAction($this->generateUrl('ekreative_new_folder'))
                        ->setMethod('POST')
                        ->add('save', 'submit')
                        ->getForm();
    }

}
