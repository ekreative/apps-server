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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/builds/")
 */
class BuildsController extends Controller
{
    /**
     * @Route("install/{token}",name="build_install")
     * @Route("install/{platform}/{token}",name="build_install_platform")
     * @Template()
     */
    public function installAction($token)
    {

        /**
         * @var App $app
         */
        $app = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppByToken($token);
        if (!$app) {
            throw new NotFoundHttpException('No build with that token');
        }

        if ($app->isType(App::TYPE_ANDROID)) {
            $url = $app->getBuildUrl();
        } elseif ($app->isType(App::TYPE_IOS)) {
            $url = 'itms-services:///?action=download-manifest&url=' . urlencode($app->getPlistUrl());

        }


        $qrcode = 'https://chart.apis.google.com/chart?chl=' . urlencode($this->generateUrl('build_install_platform', ['token' => $token, 'platform' => $app->getType()],
                true)) . '&chs=200x200&choe=UTF-8&cht=qr&chld=L%7C2';


        return ['app' => $app, 'url' => $url, 'buildUrl' => $app->getBuildUrl(), 'qrcode' => $qrcode];
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
     * @Method("POST")
     */
    public function uploadAction($project, $type)
    {
        $request = $this->getRequest();

        $form = $request->request->get('form');
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

    /**
     * This has been moved to the end because the route conflicts with the others
     *
     * @Route("{project}/{type}/",name="project_builds", requirements={"type"="^ios|android$"})
     * @Template()
     */
    public function indexAction(Request $request, $project, $type)
    {
        $currentUser = $this->getUser();
        $client = $this->get('ekreative_redmine_login.client_provider')->get($currentUser);

        $data = $client->get('projects/' . $project . '/memberships.json', [
            'query' => [
                'limit' => 100
            ]
        ])->getBody();
        $members = json_decode($data, true);

        $upload = false;
        $delete = false;

        $result = [];
        $result['title'] = 'Builds';


        foreach (array_key_exists('memberships', $members) ? $members['memberships'] : [] as $member) {
            $result['title'] = $member['project']['name'];
            $project = $member['project']['id'];

            $user = array_key_exists('user', $member) ? $member['user'] : ['id' => null];
            if ($user['id'] == $currentUser->getId()) {
                foreach ($member['roles'] as $role) {
                    if ($role['name'] == EkreativeUserRoles::ROLE_MANAGER) {
                        $delete = true;
                        $upload = true;
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
            $form = $this->newAppForm($app);
            $result['appform'] = $form->createView();
        }

        $result['type'] = $type;
        $result['delete'] = $delete;
        $result['project'] = $project;

        $result['apps'] = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppsForProject($project, $type);

        return $result;
    }

}
