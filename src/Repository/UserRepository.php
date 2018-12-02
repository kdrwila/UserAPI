<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUsersByQuery(string $query)
    {
        $validQueryKeys = array('email', 'name');

        // get only vaild query keys.
        $searchBy = array();
        $data = explode('/', $query);
        for($i = 0; $i < count($data); $i += 2)
        {
            if(in_array($data[$i], $validQueryKeys))
            {
                $searchBy[$data[$i]] = $data[$i + 1];
            }
        }

        // try recover data from memcached.
        $client = MemcachedAdapter::createConnection('memcached://localhost');
        $cache  = new MemcachedAdapter($client, 'uapi_', 0);

        // build cache identifier.
        $item   = $cache->getItem('usersList_'. 
            ($searchBy['email'] ?? '') .'_'.
            ($searchBy['name'] ?? ''));
        $users  = array();

        // cache item not found.
        if(!$item->isHit())
        {
            // build query string.
            $queryString = '';
            foreach($searchBy as $key => $value)
            {
                $queryString .= "u.$key LIKE :$key AND ";
            }
            $queryString = substr($queryString, 0, -5);

            // build query
            $queryBuilder = $this->createQueryBuilder('u')
                ->where($queryString);

            foreach($searchBy as $key => $value)
            {
                $queryBuilder = $queryBuilder->setParameter($key, "%$value%");
            }

            // get all results.
            $users = $queryBuilder->getQuery()
                ->getResult();

            // save to cache for 10 seconds.
            $item->set($users)->expiresAfter(10);
            $cache->save($item);
        }
        else $users = $item->get();

        return $users;
    }
}
