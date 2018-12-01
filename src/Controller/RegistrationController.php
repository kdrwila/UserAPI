<?php

namespace App\Controller;

use App\Form\UserType;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/sign-up", name="app_sign_up", methods={"POST"})
     */
    public function register(Request $request)
    {
        // get json from request content and decode to assoc array
        $data = json_decode($request->getContent(), true);

        if(!isset($data['name']) && !isset($data['email']) && !isset($data['password']))
        {
            $data = array(
                'message' => 'No required parameteres found ( email, password, name ).'
            );
    
            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        }

        // Build user registration form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->submit($data);
        if($form->isValid())
        {
            // generate new API token for user
            $apiToken = $user->generateNewAPIToken();

            // save new user
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // manually authenticate user
            $token = new UsernamePasswordToken($user, null, 'api_logged', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_api_logged', serialize($token));

            $data = array(
                'message'   => "Registration was successful, you API key is: $apiToken, in case if automatic authorization doesn't work set as 'X-AUTH-TOKEN' in header.",
                'id'        => $user->getId(),
                'apiToken'  => $apiToken
            );
    
            return new JsonResponse($data, Response::HTTP_CREATED);
        } 
        else
        {
            $data = array(
                'message' => "Registration form isn't valid, please fix following errors:",
                'errors' => (STRING)$form->getErrors()
            );
    
            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        }
    }
}

?>