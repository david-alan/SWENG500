<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class UserVerifyController extends Controller{

    private $status = 'failed';

    /**
     * @Route("/verifyUser", name="verifyUser")
     * @Method("POST")
     */
    public function verifyUserAction(Request $request)
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneByEmail($email);

        if (!$user) {
            return $this->validationFailed();
        }

       return $this->comparePassword($user, $password);
    }

    private function comparePassword($user, $userSubmittedPassword)
    {
        if($this->container->get('password_service')->verifyPassword($user, $userSubmittedPassword)) {
            return $this->validationSucceeded();
        } else {
            return $this->validationFailed();
        }
    }

    private function validationFailed()
    {
        return new Response(
            '{"results":"failed"}',
            200,
            array('Content-Type' => 'application/json')
        );
    }

    private function validationSucceeded()
    {
        return new Response(
            '{"results":"success"}',
            200,
            array('Content-Type' => 'application/json')
        );
    }
}