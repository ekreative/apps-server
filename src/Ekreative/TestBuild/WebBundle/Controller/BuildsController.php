<?php

namespace Ekreative\TestBuild\WebBundle\Controller;

use Ekreative\TestBuild\CoreBundle\Entity\App;
use Ekreative\TestBuild\CoreBundle\Roles\EkreativeUserRoles;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/builds/")
 */
class BuildsController extends Controller
{
    /**
     * @Route("install/{token}", name="build_install")
     * @Route("install/{platform}/{token}", name="build_install_platform")
     */
    public function installAction($token)
    {
        $app = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppByToken($token);
        if (!$app) {
            throw new NotFoundHttpException('No build with that token');
        }

        return $this->renderApp($app);
    }

    /**
     * @Route("installByCommit/{commit}/{jobName}", requirements={"commit"="^[0-9a-f]{40}$", "jobName"="^[0-9a-z-_]+$"}, defaults={"jobName"=null})
     */
    public function commitAction($commit, $jobName = null)
    {
        $app = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppByCommit($commit, $jobName);
        if (!$app) {
            throw new NotFoundHttpException('No build with that commit');
        }

        return $this->renderApp($app);
    }

    /**
     * @Route("{projectSlug}/{type}/{ref}/{jobName}", requirements={"type"="^ios|android$", "ref"="^[0-9a-z-]+$", "jobName"="^[0-9a-z-_]+$"}, defaults={"jobName"=null})
     */
    public function latestAction($projectSlug, $type, $ref, $jobName = null)
    {
        list($projectId, $upload, $delete, $projectName) = $this->getProjectIdAndPermissions($projectSlug);

        $app = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppForProject($projectId, $type, $ref, $jobName);
        if (!$app) {
            throw new NotFoundHttpException('No build on that branch');
        }

        return $this->renderApp($app);
    }

    private function renderApp(App $app)
    {
        if ($app->isType(App::TYPE_ANDROID)) {
            $url = $app->getBuildUrl();
        } elseif ($app->isType(App::TYPE_IOS)) {
            $url = 'itms-services:///?action=download-manifest&url=' . urlencode($app->getPlistUrl());
        }

        $install = $this->generateUrl('build_install_platform', [
            'token' => $app->getToken(),
            'platform' => $app->getType(),
        ], true);
        $qrcode = 'https://chart.apis.google.com/chart?chl=' . urlencode($install) . '&chs=200x200&choe=UTF-8&cht=qr&chld=L%7C2';

        return $this->render('@EkreativeTestBuildWeb/Builds/install.html.twig', [
            'app' => $app,
            'url' => $url,
            'buildUrl' => $app->getBuildUrl(),
            'qrcode' => $qrcode,
            'install' => $install,
        ]);
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
            $this->generateUrl('project_builds', ['type' => $app->getType(), 'projectSlug' => $app->getProjectId()])
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

        return $this->redirect($this->generateUrl('project_builds', ['type' => $type, 'projectSlug' => $project]));
    }

    /**
     * @Route("upload/{project}/{type}",name="upload")
     * @Method("POST")
     */
    public function uploadAction($project, $type, Request $request)
    {
        $form = $request->request->get('form');
        $files = $request->files->get('form');

        $buildsUploader = $this->get('ekreative_test_build_core.builds_uploader');
        $app = $buildsUploader->upload($files['build'], $form['comment'], $project, $type);

        return $this->redirect($this->generateUrl('project_builds', ['type' => $app->getType(), 'projectSlug' => $app->getProjectId()]));
    }

    private function newAppForm(App $app)
    {
        $form = $this->createFormBuilder($app)
            ->add('build', 'file', ['attr' => ['placeholder' => 'version', 'class' => 'form-control']])
            ->add('comment', 'text', ['required' => false, 'attr' => ['placeholder' => 'comment', 'class' => 'form-control']])
            ->setAction($this->generateUrl('upload', ['type' => $app->getType(), 'project' => $app->getProjectId()]))
            ->setMethod('POST')
            ->add('save', 'submit', ['label' => 'Upload']);

        return $form->getForm();
    }

    /**
     * This has been moved to the end because the route conflicts with the others.
     *
     * @Route("{projectSlug}/{type}/",name="project_builds", requirements={"type"="^ios|android$"})
     * @Template()
     */
    public function indexAction($projectSlug, $type)
    {
        list($projectId, $upload, $delete, $projectName) = $this->getProjectIdAndPermissions($projectSlug);

        $result = [];

        if ($upload) {
            $app = new App();
            $app->setType($type);
            $app->setProjectId($projectId);
            $form = $this->newAppForm($app);
            $result['appform'] = $form->createView();
        }

        $result['title'] = $projectName;

        $result['type'] = $type;
        $result['delete'] = $delete;
        $result['project'] = $projectId;

        $result['apps'] = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppsForProject($projectId, $type);

        return $result;
    }

    private function getProjectIdAndPermissions($projectSlug)
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw new AccessDeniedHttpException('You must be logged in to access this resource');
        }
        $client = $this->get('ekreative_redmine_login.client_provider')->get($currentUser);

        $data = $client->get('projects/' . $projectSlug . '/memberships.json', [
            'query' => [
                'limit' => 100
            ]
        ])->getBody();
        $members = json_decode($data, true);

        $upload = false;
        $delete = false;

        $projectName = null;
        $projectId = null;

        foreach (array_key_exists('memberships', $members) ? $members['memberships'] : [] as $member) {
            $projectName = $member['project']['name'];
            $projectId = $member['project']['id'];

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

        if (!$projectId) {
            throw new NotFoundHttpException("Project with slug not found $projectSlug");
        }

        return [$projectId, $upload, $delete, $projectName];
    }
}
