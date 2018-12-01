<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

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

        $searchBy = array();
        $data = explode('/', $query);
        for($i = 0; $i < count($data); $i += 2)
        {
            if(in_array($data[$i], $validQueryKeys))
            {
                $searchBy[$data[$i]] = $data[$i + 1];
            }
        }

        $queryString = '';
        foreach($searchBy as $key => $value)
        {
            $queryString .= "u.$key LIKE :$key AND ";
        }
        $queryString = substr($queryString, 0, -5);

        $queryBuilder = $this->createQueryBuilder('u')
            ->where($queryString);

        foreach($searchBy as $key => $value)
        {
            $queryBuilder = $queryBuilder->setParameter($key, "%$value%");
        }

        return $queryBuilder->getQuery()
            ->getResult();
    }
}
