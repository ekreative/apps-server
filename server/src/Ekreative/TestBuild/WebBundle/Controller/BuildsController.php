<?php

namespace Ekreative\TestBuild\WebBundle\Controller;

use Ekreative\TestBuild\CoreBundle\Entity\App;
use Ekreative\TestBuild\CoreBundle\Roles\EkreativeUserRoles;
use Ekreative\TestBuild\CoreBundle\Services\IpaReader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
/**
 * @Route("/builds/")
 */
class BuildsController extends Controller
{
    /**
     * @Route("{project}/{type}/",name="project_builds", requirements={"project": "\d+"})
     * @Template()
     */
    public function indexAction(Request $request, $project, $type)
    {

        $currentUser = $this->getUser();
        $session     = $request->getSession();
        $projects    = $session->get('projects');

        $data    = $this->get('ekreative_redmine_login.client_provider')->get($currentUser)->get('projects/' . $project . '/memberships.json')->getBody();
        $members = json_decode($data, true);

        $upload = false;
        $delete = false;

        $result          = [];
        $result['title'] = 'Builds';

        if (is_array($projects)) {
            foreach ($projects as $projectArr) {
                if ($projectArr['id'] == $project) {
                    $result['title'] = $projectArr['name'];
                }
            }
        }

        foreach (array_key_exists('memberships', $members) ? $members['memberships'] : [] as $member) {
            $user = array_key_exists('user', $member) ? $member['user'] : ['id' => null];
            if ($user['id'] == $currentUser->getId()) {
                foreach ($member['roles'] as $role) {
                    if ($role['name'] == EkreativeUserRoles::ROLE_MANAGER) {
                        $delete = true;
                    }
                    if ($role['name'] == EkreativeUserRoles::ROLE_DEVELOPER) {
                        $delete = true;
                        $upload = true;
                    }
                }
            }
        }

        if ($upload) {
            $app = new App();
            $app->setType($type);
            $app->setProjectId($project);
            $form              = $this->newAppForm($app);
            $result['appform'] = $form->createView();
        }

        $result['type']   = $type;
        $result['delete'] = $delete;
        $result['apps']   = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppsForProject($project, $type);

        return $result;
    }

    /**
     * @Route("install/{token}",name="build_install")
     * @Template()
     */
    public function installAction($token)
    {

        /**
         * @var App $app
         */
        $app = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppByToken($token);


        if ($app->isType(App::TYPE_ANDROID)) {
            $url = $app->getBuildUrl();
        } elseif ($app->isType(App::TYPE_IOS)) {
            $url = 'itms-services:///?action=download-manifest&url=' . urlencode($app->getPlistUrl());

        }


        $qrcode = 'http://chart.apis.google.com/chart?chl=' . urlencode($this->generateUrl('build_install', ['token' => $token],
                true)) . '&chs=200x200&choe=UTF-8&cht=qr&chld=L%7C2';


        return ['app' => $app, 'url' => $url, 'qrcode' => $qrcode];
    }

    /**
     * @Route("release/{app}",name="build_inverse_release")
     * @Template()
     */
    public function releaseAction(App $app)
    {

        $app->inverseRelease();

        $em = $this->getDoctrine()->getManager();
        $em->persist($app);
        $em->flush();

        return $this->redirect(
            $this->generateUrl('project_builds', ['type' => $app->getType(), 'project' => $app->getProjectId()])
        );
    }

    /**
     * @Route("delete/{project}/{type}/{token}",name="build_delete")
     * @Template()
     * @Method("POST")
     */
    public function deleteAction($project, $type, $token)
    {

        $s3Service = $this->get('ekreative_test_build_core.file_uploader');

        $app = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppByToken($token);

        $em = $this->getDoctrine()->getManager();

        $s3Service->delete($app->getFilename());

        if ($app->isType(App::TYPE_ANDROID)) {
            $s3Service->delete($app->getPlistName());
        }


        $em->remove($app);
        $em->flush();

        return $this->redirect($this->generateUrl('project_builds', ['type' => $type, 'project' => $project]));

    }

    /**
     * @Route("upload/{project}/{type}",name="upload")
     */
    public function uploadAction($project, $type)
    {
        $request = $this->getRequest();

        $form  = $request->request->get('form');
        $files = $request->files->get('form');

        $buildsUploader = $this->get('ekreative_test_build_core.builds_uploader');
        $app = $buildsUploader->upload($files['build'], $form['comment'], $project, $type);

        return $this->redirect($this->generateUrl('project_builds', ['type' => $app->getType(), 'project' => $app->getProjectId()]));

    }

    private function newAppForm(App $app)
    {
        $form = $this->createFormBuilder($app)
                     ->add('build', 'file', array('attr' => array('placeholder' => 'version', 'class' => "form-control")))
                     ->add('comment', 'text', array('required' => false, 'attr' => array('placeholder' => 'comment', 'class' => "form-control")))
                     ->setAction($this->generateUrl('upload', ['type' => $app->getType(), 'project' => $app->getProjectId()]))
                     ->setMethod('POST')
                     ->add('save', 'submit', ['label' => 'Upload']);

        return $form->getForm();
    }


}
