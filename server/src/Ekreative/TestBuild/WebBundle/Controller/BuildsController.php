<?php

namespace Ekreative\TestBuild\WebBundle\Controller;

use Ekreative\TestBuild\CoreBundle\Entity\App;
use Ekreative\TestBuild\CoreBundle\Roles\EkreativeUserRoles;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class BuildsController extends Controller
{
    /**
     * @Route("project/{project}/{type}/",name="project_builds")
     * @Template()
     */
    public function indexAction(Request $request, $project, $type)
    {

        $currentUser = $this->getUser();
        $session = $request->getSession();
        $projects = $session->get('projects');


        $data = $this->get('ekreative_redmine_login.client_provider')->get($currentUser)->get('projects/'.$project.'/memberships.json')->getBody();
        $members = json_decode($data, true);

        $upload = false;
        $delete = false;


        $result = [];
        $result['title'] = 'Builds';

        if(is_array($projects)){
            foreach($projects as $projectArr){
                if($projectArr['id']==$project){
                    $result['title']=$projectArr['name'];
                }
            }
        }

        foreach(array_key_exists('memberships',$members)?$members['memberships']:[] as $member ){
            $user = array_key_exists('user',$member) ? $member['user']:['id'=>null];
            if( $user['id']==$currentUser->getId()){
                foreach($member['roles'] as $role){
                    if($role['name']==EkreativeUserRoles::ROLE_MANAGER){
                        $delete = true;
                    }
                    if($role['name']==EkreativeUserRoles::ROLE_DEVELOPER){
                        $delete = true;
                        $upload = true;
                    }
               }
            }
        }

        if($upload){
            $app = new App();
            $app->setType($type);
            $app->setProjectId($project);
            $form = $this->newAppForm($app);
            $result['appform']  =  $form->createView();
        }

        $result['type']  =  $type;
        $result['delete']  = $delete;
        $result['apps']  =  $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppsForProject($project, $type);

        return $result;
    }

    /**
     * @Route("install/{token}",name="build_install")
     * @Template()
     */
    public function installAction($token)
    {
        $app = $this->getDoctrine()->getRepository('EkreativeTestBuildCoreBundle:App')->getAppByToken($token);


       $url =  'itms-services:///?action=download-manifest&url='.urlencode($app->getPlistUrl());

        $qrcode = $this->generateUrl('build_install', ['token' => $token]);

        return ['app' => $app,'plistUrl'=>$url, 'qrcode' =>$qrcode ];
    }

    /**
     * @Route("delete/{project}/{type}/{token}",name="build_delete")
     * @Template()
     * @Method("POST")
     */
    public function deleteAon($project, $type, $token)
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

        /**
         * @var RedmineUser $user
         */

        $user = $this->getUser();
        $em   = $this->getDoctrine()->getManager();

        $app = new App();
        $app->setCreated(new \DateTime());

        $app->setProjectId($project);
        $app->setType($type);

        $form = $request->request->get('form');

        $files = $request->files->get('form');

        $app->setVersion($form['version']);
        $app->setComment($form['comment']);

        if ($app->isType(App::TYPE_IOS)) {
            $app->setBundleId($form['bundleId']);
        } else {
            $app->setBundleId('dasd');
        }


        $app->setBuild($files['build']);


        $app->setCreatedName($user->getFirstName() . '  ' . $user->getLastName());
        $app->setCreatedId($user->getId());

        $icon  = $app->getIcon();
        $build = $app->getBuild();

        $app->setName($build->getClientOriginalName());
        $em->persist($app);


        if ($app->isType(App::TYPE_IOS)) {
            $headers = array(
                'ContentType'        => 'application/octet-stream',
                'ContentDisposition' => 'attachment;filename="' . $app->getDownloadNameFilename() . '"'
            );
        } else if ($app->isType(App::TYPE_ANDROID)) {
            $headers = [
                'ContentDisposition' => 'attachment;filename="' . $app->getDownloadNameFilename() . '"',
                'ContentType'        => 'application/vnd.android.package-archive'
            ];
        }

        $s3Service = $this->get('ekreative_test_build_core.file_uploader');

        if ($icon) {
            $app->setIconUrl($s3Service->upload($icon->getRealPath(), $app->getIconFileName()));
            unlink($icon->getRealPath());
        }

        $app->setBuildUrl($s3Service->upload($build->getRealPath(), $app->getFilename(), $headers));
        unlink($build->getRealPath());


        if ($app->isType(App::TYPE_IOS)) {
            $tempFile = tempnam("/tmp", "plist");
            $plist    = $this->getDoctrine()
                             ->getRepository('EkreativeTestBuildCoreBundle:App')
                             ->getPlistString(
                                 $app->getBuildUrl(),
                                 $app->getBundleId(),
                                 $app->getVersion(),
                                 $build->getFilename());

            file_put_contents($tempFile, $plist);
            $app->setPlistUrl($s3Service->upload($tempFile, $app->getPlistName(), $headers));
            unlink($tempFile);
        }

        $app->setQrcodeUrl('http://chart.apis.google.com/chart?chl=' . urlencode($this->generateUrl('build_install',
                ['token' => $app->getToken()])) . '&chs=200x200&choe=UTF-8&cht=qr&chld=L%7C2');
        $em->persist($app);
        $em->flush();

        return $this->redirect($this->generateUrl('project_builds', ['type' => $app->getType(), 'project' => $app->getProjectId()]));

    }

    private function newAppForm(App $app)
    {
        $form = $this->createFormBuilder($app)
                     ->add('build', 'file', array('attr' => array('placeholder' => 'version', 'class' => "form-control")))
                     ->add('version', 'text', array('required' => false, 'attr' => array('placeholder' => 'version', 'class' => "form-control")))
                     ->add('comment', 'text', array('required' => false, 'attr' => array('placeholder' => 'comment', 'class' => "form-control")))
                     ->setAction($this->generateUrl('upload', ['type' => $app->getType(), 'project' => $app->getProjectId()]))
                     ->setMethod('POST')
                     ->add('save', 'submit', ['label' => 'Upload']);
        if ($app->isType(App::TYPE_IOS)) {
            $form->add('bundleId', 'text', array('required' => true, 'attr' => array('placeholder' => 'bundleIdentifier', 'class' => "form-control")));
        }

        return $form->getForm();
    }


}
