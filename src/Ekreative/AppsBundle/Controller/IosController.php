<?php

namespace Ekreative\AppsBundle\Controller;

use Ekreative\AppsBundle\Entity\IosFolder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Ekreative\AppsBundle\Entity\IosApp;

class IosController extends BaseController {

    public function indexAction(Request $request, $id) {

        $folder = $this->getDoctrine()->getRepository('EkreativeAppsBundle:IosFolder')->find($id);
        if ($folder) {

            $em = $this->getDoctrine()->getManager();

            $app = new IosApp();
            $app->setFolder($folder);
            $form = $this->newAppForm($app);
            
            $folderType = $this->getCurrentFolderType();

            return $this->render('EkreativeAppsBundle:Ios:appsList.html.twig', array(
                        'folder' => $folder,
                        'appform' => $form->createView(),
                        'currentFolderType' => BaseController::FOLDER_ANDROID,
                        'serveLink' => $this->serveLink($folderType)
                            )
            );
        }

        throw new NotFoundHttpException("Page not found");
    }

    private function newAppForm($entity) {
        return $this->createFormBuilder($entity)
                        ->add('uploadedFile', 'file', array('attr' => array('placeholder' => 'version', 'class' => "form-control")))
                        ->add('version', 'text', array('required' => false, 'attr' => array('placeholder' => 'version', 'class' => "form-control")))
                        ->add('bundleIdentifier', 'text', array('required' => true, 'attr' => array('placeholder' => 'bundleIdentifier', 'class' => "form-control")))
                        ->add('comment', 'text', array('required' => false, 'attr' => array('placeholder' => 'comment', 'class' => "form-control")))
                        ->setAction($this->generateUrl('ekreative_app_new', array('id' => $entity->getFolder()->getId())))
                        ->setMethod('POST')
                        ->add('save', 'submit')
                        ->getForm();
    }

}
