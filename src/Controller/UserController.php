<?php
namespace App\Controller;

use App\Form\UserUpdateType;
use App\Entity\User;
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
    public function getMe()
    {
        $user       = $this->getUser();
        $assocUser  = $user->toAssocPublic();

        // not sure if we want to show all the data about user
        // $encoders = array(new XmlEncoder(), new JsonEncoder());
        // $normalizers = array(new ObjectNormalizer());

        // $serializer = new Serializer($normalizers, $encoders);

        // $assocUser = json_decode($serializer->serialize($user, 'json'), true);

        return new JsonResponse($assocUser, Response::HTTP_OK);
    }

    /**
     * @Route("/api/users", name="app_users")
     */
    public function getUsers()
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

        $assocUsers = array();
        foreach($users as $u)
        {
            $assocUsers[] = $u->toAssocPublic();
        }

        return new JsonResponse($assocUsers, Response::HTTP_OK);
    }

    /**
     * @Route("/api/users", name="app_users")
     */
    public function getUsersByQuery(string $query)
    {
        $em     = $this->getDoctrine()->getEntityManager();
        $users  = $em->getRepository(User::class)
            ->loadUsersByQuery($query);

        $assocUsers = array();
        foreach($users as $u)
        {
            $assocUsers[] = $u->toAssocPublic();
        }

        return new JsonResponse($assocUsers, Response::HTTP_OK);
    }

    /**
     * @Route("/api/user/{id<\d+>}", name="app_user", methods={"GET"})
     */
    public function getUserById(User $user)
    {
        $assocUser = $user->toAssocPublic();

        return new JsonResponse($assocUser, Response::HTTP_OK);
    }

    /**
     * @Route("/api/user/{id<\d+>}", name="app_user_update", methods={"POST", "PUT"})
     */
    public function postUser(User $user, Request $request)
    {
        if($this->getUser() != $user)
        {
            $data = array(
                'message' => "Specified user account doesn't belong to you."
            );
    
            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        }
 
        // get json data from request content
        $data = json_decode($request->getContent(), true);

        if(!isset($data['name']) && !isset($data['password']))
        {
            $data = array(
                'message' => 'No required parameteres found ( password, name ).'
            );
    
            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
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
                'message' => "User account data with id: $id was updated"
            );
    
            return new JsonResponse($data, Response::HTTP_OK);
        }
        else
        {
            $data = array(
                'message'   => "User update form isn't valid, please fix following errors:",
                'errors'    => (STRING)$form->getErrors()
            );
    
            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/api/user/{id<\d+>}", name="app_user_delete", methods={"DELETE"})
     */
    public function deleteUser(User $user)
    {
        if($this->getUser() == $user)
        {
            $data = array(
                'message' => "You can't delete your user account."
            );
    
            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($user);
        $em->flush();

        $data = array(
            'message' => "User account with id: $id was removed."
        );

        return new JsonResponse($data, Response::HTTP_OK);
    }
}

?>