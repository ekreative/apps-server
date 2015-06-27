<?php

namespace Ekreative\TestBuild\WebBundle\Controller;

use Ekreative\TestBuild\CoreBundle\Entity\App;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BuildsController extends Controller
{
    /**
     * @Route("project/{project}/{type}/",name="project_builds")
     * @Template()
     */
    public function indexAction($project, $type)
    {
        $apps = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppsForProject($project, $type);

        $app = new App();
        $app->setType($type);
        $form = $this->newAppForm($app);

        return ['apps' => $apps, 'appform' => $form->createView(), 'type' => $type];
    }

    /**
     * @Route("install/{token}",name="build_install")
     * @Template()
     */
    public function installAction($token)
    {
        $app = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppByToken($token);

        return ['app' => $app];
    }

    /**
     * @Route("delete/{token}",name="build_install")
     * @Template()
     * @Method("POST")
     */
    public function deleteAction($token)
    {
        $app = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppByToken($token);

        return ['app' => $app];
    }

    /**
     * @Route("upload/{type}",name="upload")
     * @Template()
     */
    public function uploadAction($type)
    {
        $apps = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppsForProject($project, $type);

        $app = new App();
        $app->setType($type);
        $form = $this->newAppForm($app);

        return ['apps' => $apps, 'appform' => $form->createView(), 'type' => $type];
    }

    private function newAppForm(App $app)
    {
        $form = $this->createFormBuilder($app)
                     ->add('build', 'file', array('attr' => array('placeholder' => 'version', 'class' => "form-control")))
                     ->add('version', 'text', array('required' => false, 'attr' => array('placeholder' => 'version', 'class' => "form-control")))
                     ->add('comment', 'text', array('required' => false, 'attr' => array('placeholder' => 'comment', 'class' => "form-control")))
                     ->setAction($this->generateUrl('upload', array('type' => $app->getType())))
                     ->setMethod('POST')
                     ->add('save', 'submit', ['label' => 'Upload']);
        if ($app->isType(App::TYPE_IOS)) {
            $form->add('bundleId', 'text', array('required' => true, 'attr' => array('placeholder' => 'bundleIdentifier', 'class' => "form-control")));
        }

        return $form->getForm();
    }


}
