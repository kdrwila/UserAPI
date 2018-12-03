<?php

namespace App\Security;

use App\Entity\User;
use App\HttpFoundation\ResponseAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class LoginAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $user;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'app_sign_in' && $request->isMethod('POST');
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        $data       = json_decode($request->getContent(), true);

        $email      = $data['email'] ?? '';
        $password   = $data['password'] ?? '';

        return array(
            'email'     => $email,
            'password'  => $password
        );
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        // if a User object, checkCredentials() is called

        if(empty($credentials['email']) || empty($credentials['password']))
        {
            throw new AuthenticationException('There are some missing parameters ( expected password and email )');
        }

        return $this->em->getRepository(User::class)
            ->findOneBy(['email' => $credentials['email']]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if($credentials['password'] == $user->getPassword())
        {
            $this->user = $user;
            return true;
        }
        else
        {
            throw new AuthenticationException('Password is incorrect.');
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $apiToken = $this->user->generateNewAPIToken();
        $responseType = $request->attributes->get('_route_params')['responseType'];

        // update api token
        $this->em->flush();

        $data = array(
            'message'   => "You are successfully logged in. Your new API token in case if automatic authentication doesn't work is: $apiToken.",
            'id'        => $this->user->getId(),
            'apiToken'  => $apiToken
        );

        $adapter = new ResponseAdapter($data, Response::HTTP_OK, array(), $responseType);
        return $adapter->returnResponse();
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $responseType = $request->attributes->get('_route_params')['responseType'];

        $data = array(
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        );

        $adapter = new ResponseAdapter($data, Response::HTTP_FORBIDDEN, array(), $responseType);
        return $adapter->returnResponse();
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $responseType = $request->attributes->get('_route_params')->get('responseType');

        $data = array(
            'message' => 'Authentication Required. Create new account (/api/sign-up) or login (/api/sign-in)'
        );

        $adapter = new ResponseAdapter($data, Response::HTTP_UNAUTHORIZED, array(), $responseType);
        return $adapter->returnResponse();
    }

    public function supportsRememberMe()
    {
        return false;
    }
}

?>