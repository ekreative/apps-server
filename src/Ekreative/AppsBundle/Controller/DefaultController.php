<?php

namespace Ekreative\AppsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller {


    public function indexAction() {

//        $folder = new \Ekreative\AppsBundle\Entity\Folder();
//        $folder->setDate(new \DateTime());
//
//        $form = $this->newFolderForm($folder);
//        $folders = $this->getDoctrine()->getRepository('EkreativeAppsBundle:AndroidFolder')->getFolders()->getQuery()->getResult();
        return $this->render('EkreativeAppsBundle:Default:index.html.twig', array(
//                    'folders' => $folders,
//                    'folderform' => $form->createView(),
                        )
        );
    }


}
