<?php

namespace Ekreative\AppsBundle\Controller;

use Ekreative\AppsBundle\Entity\AndroidFolder;
use Ekreative\AppsBundle\Entity\IosFolder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class FolderController extends BaseController {

    public function switchAction($to) {
        $session = $session = $this->get('session');
        $session->set(self::SESSION_KEY, ucfirst($to));
        return new RedirectResponse($this->generateUrl('ekreative_folder_index'));
    }

    public function indexAction() {

        $folderType = $this->getCurrentFolderType();
        
        return $this->render('EkreativeAppsBundle:Folder:foldersList.html.twig', array(
                    'folders' => $this->getRepository($folderType)->findBy([],['name'=>'ASC']),
                    'folderform' => $this->newFolderForm($folderType)->createView(),
                    'currentFolderType' => $folderType,
                    'serveLink' => $this->serveLink($folderType)
        ));
    }

    public function serveAction($id) {

        $currentFolderType = $this->getCurrentFolderType();

        $folder = $this->getRepository($currentFolderType)->find($id);

        return $this->render('EkreativeAppsBundle:' . $currentFolderType . ':appsList.html.twig', array(
                    'folder' => $folder
        ));
    }

    private function getRepository($currentFolderType) {
        if ($currentFolderType == self::FOLDER_ANDROID) {
            return $this->getDoctrine()->getRepository('EkreativeAppsBundle:AndroidFolder');
        } elseif ($currentFolderType == self::FOLDER_IOS) {
            return $this->getDoctrine()->getRepository('EkreativeAppsBundle:IosFolder');
        }
    }

    public function newfolderAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $currentFolderType = $this->getCurrentFolderType($request);

        $abstractFolder = new \Ekreative\AppsBundle\Entity\Folder();
        //lilte hack in newFolderForm...
        $form = $this->newFolderForm($currentFolderType, $abstractFolder);
        $form->handleRequest($request);
        $em->persist($abstractFolder);
        $em->flush();

        return new RedirectResponse($this->generateUrl('ekreative_folder_index'));
    }

    public function deleleFolderAction($id) {

        $folder = $this->getDoctrine()->getRepository('EkreativeAppsBundle:Folder')->find($id);

        if ($folder) {
            $em = $this->getDoctrine()->getManager();
            if (count($folder->getApp()) == 0) {
                $em->remove($folder);
                $em->flush();
            }
        }

        return new RedirectResponse($this->generateUrl('ekreative_folder_index'));
    }

    private function newFolderForm($currentFolderType, &$inputFolder = null) {

        if ($currentFolderType == self::FOLDER_ANDROID) {
            $folder = new AndroidFolder();
        } elseif ($currentFolderType == self::FOLDER_IOS) {
            $folder = new IosFolder();
        }

        $folder->setDate(new \DateTime());
        $inputFolder = $folder;
        return $this->createFormBuilder($folder)
                        ->add('name', 'text')
                        ->setAction($this->generateUrl('ekreative_new_folder'))
                        ->setMethod('POST')
                        ->add('save', 'submit', ['label' => 'Create'])
                        ->getForm();
    }

}
