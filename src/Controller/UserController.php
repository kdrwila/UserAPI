<?php
namespace App\Controller;

use App\Form\UserUpdateType;
use App\Entity\User;
use App\HttpFoundation\ResponseAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\Serializer\Serializer;
// use Symfony\Component\Serializer\Encoder\XmlEncoder;
// use Symfony\Component\Serializer\Encoder\JsonEncoder;
// use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserController extends AbstractController
{    
    /**
     * @Route("/api/me", name="app_me")
     */
    public function getMe(string $responseType)
    {
        $user       = $this->getUser();
        $assocUser  = $user->toAssocPublic(true);

        // not sure if we want to show all the data about user
        // $encoders = array(new XmlEncoder(), new JsonEncoder());
        // $normalizers = array(new ObjectNormalizer());

        // $serializer = new Serializer($normalizers, $encoders);

        // $assocUser = json_decode($serializer->serialize($user, 'json'), true);

        $adapter = new ResponseAdapter($assocUser, Response::HTTP_OK, array(), $responseType);
        return $adapter->returnResponse();
    }

    /**
     * @Route("/api/users", name="app_users")
     */
    public function getUsers(string $responseType)
    {
        // can't test it on windows
        // $client = MemcachedAdapter::createConnection('memcached://localhost');
        // $cache  = new MemcachedAdapter($client, 'uapi_', 0);
        // $item   = $cache->getItem('usersList');
        // $users  = array();

        // if(!$item->isHit())
        // {
        //     $em     = $this->getDoctrine()->getEntityManager();
        //     $users  = $em->getRepository(User::class)->findAll();

        //     $item
        //         ->set($users)
        //         ->expiresAfter(10);
        //     $cache->save($item);
        // }
        // else
        // {
        //     $users = $item->get();
        // }

        $em     = $this->getDoctrine()->getEntityManager();
        $users  = $em->getRepository(User::class)->findAll();

        $assocUsers = array('users' => array());
        foreach($users as $u)
        {
            $assocUsers['users'][] = $u->toAssocPublic();
        }

        $adapter = new ResponseAdapter($assocUsers, Response::HTTP_OK, array(), $responseType);
        return $adapter->returnResponse();
    }

    /**
     * @Route("/api/users", name="app_users")
     */
    public function getUsersByQuery(string $query, string $responseType)
    {
        $em     = $this->getDoctrine()->getEntityManager();
        $users  = $em->getRepository(User::class)
            ->loadUsersByQuery($query);

        $assocUsers = array('users' => array());
        foreach($users as $u)
        {
            $assocUsers['users'][] = $u->toAssocPublic();
        }

        $adapter = new ResponseAdapter($assocUsers, Response::HTTP_OK, array(), $responseType);
        return $adapter->returnResponse();
    }

    /**
     * @Route("/api/user/{id<\d+>}", name="app_user", methods={"GET"})
     */
    public function getUserById(User $user, string $responseType)
    {
        $assocUser = $user->toAssocPublic(true);

        $adapter = new ResponseAdapter($assocUser, Response::HTTP_OK, array(), $responseType);
        return $adapter->returnResponse();
    }

    /**
     * @Route("/api/user/{id<\d+>}", name="app_user_update", methods={"POST", "PUT"})
     */
    public function postUser(User $user, string $responseType, Request $request)
    {
        if($this->getUser() != $user)
        {
            $data = array(
                'message' => "Specified user account doesn't belong to you."
            );
    
            $adapter = new ResponseAdapter($data, Response::HTTP_FORBIDDEN, array(), $responseType);
            return $adapter->returnResponse();
        }
 
        // get json data from request content
        $data = json_decode($request->getContent(), true);

        if(!isset($data['name']) || !isset($data['password']))
        {
            $data = array(
                'message' => 'No required parameteres found ( password, name ).'
            );
    
            $adapter = new ResponseAdapter($data, Response::HTTP_BAD_REQUEST, array(), $responseType);
            return $adapter->returnResponse();
        }
        
        // build update form
        $form = $this->createForm(UserUpdateType::class, $user);
        $form->submit($data);

        if($form->isValid()) 
        {
            // update changes
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $data = array(
                'message' => "User account data with id: $user->id was updated"
            );
    
            $adapter = new ResponseAdapter($data, Response::HTTP_OK, array(), $responseType);
            return $adapter->returnResponse();
        }
        else
        {
            $data = array(
                'message'   => "User update form isn't valid, please fix following errors:",
                'errors'    => (STRING)$form->getErrors(true)
            );
    
            $adapter = new ResponseAdapter($data, Response::HTTP_BAD_REQUEST, array(), $responseType);
            return $adapter->returnResponse();
        }
    }

    /**
     * @Route("/api/user/{id<\d+>}", name="app_user_delete", methods={"DELETE"})
     */
    public function deleteUser(User $user, string $responseType)
    {
        if($this->getUser() == $user)
        {
            $data = array(
                'message' => "You can't delete your user account."
            );
    
            $adapter = new ResponseAdapter($data, Response::HTTP_BAD_REQUEST, array(), $responseType);
            return $adapter->returnResponse();
        }

        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($user);
        $em->flush();

        $data = array(
            'message' => "User account with id: $user->id was removed."
        );

        $adapter = new ResponseAdapter($data, Response::HTTP_OK, array(), $responseType);
        return $adapter->returnResponse();
    }
}

?>