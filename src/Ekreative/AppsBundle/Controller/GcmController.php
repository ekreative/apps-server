<?php

namespace Ekreative\AppsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class GcmController extends Controller {

    private $example = ['data' => 'value','array'=>[]];

    public function indexAction(Request $request) {

        $gcm = new \Ekreative\AppsBundle\Entity\Gcm();

        $gcm->setData(json_encode($this->example));
        $form = $this->gcmForm($gcm);
        $form->handleRequest($request);
        $result = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $tokens = explode("\n", $gcm->getDevicetoken());
            if (count($tokens) == 0) {
                $tokens = array($gcm->getDevicetoken());
            }
            $result = $this->sendCGM($gcm->getData(), $tokens, $gcm->getApikey());
        }
        return $this->render('EkreativeAppsBundle:Gcm:index.html.twig', array(
                    'result' => print_r(json_decode($result), true),
                    'form' => $form->createView()));
    }

    private function gcmForm($gcm) {
        return $this->createFormBuilder($gcm)
                        ->add('apikey', 'text')
                        ->add('devicetoken', 'textarea')
                        ->add('data', 'textarea')
                        ->setAction($this->generateUrl('ekreative_gcmtest'))
                        ->setMethod('POST')
                        ->add('save', 'submit', ['label' => 'Send'])
                        ->getForm();
    }

        private function sendCGM($data, $registrationIDs, $key) {
            $fields = array(
                'registration_ids' => $registrationIDs,
                'data' => json_decode($data),
            );

            $headers = array(
                'Authorization: key=' . $key,
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        }

}
