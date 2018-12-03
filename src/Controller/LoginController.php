<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\HttpFoundation\ResponseAdapter;

class LoginController extends AbstractController
{
    /**
     * @Route("/sign-in", name="app_sign_in", methods={"POST"})
     */
    public function login()
    {
        throw new \Exception('Don\'t forget to activate LoginAuthenticator in security.yaml');
    }   

    /**
    * @Route("/sign-out", name="app_sign_out")
    */
    public function logout()
    {
        throw new \Exception('Don\'t forget to activate logout path in security.yaml');
    }   

    /**
    * @Route("/signed-out", name="app_sign_out")
    */
    public function loggedout()
    {
        $data = array(
            'message' => "You were successfully logged out"
        );

        $adapter = new ResponseAdapter($data, Response::HTTP_BAD_REQUEST, array(), 'json');
        return $adapter->returnResponse();
    }
}

?>