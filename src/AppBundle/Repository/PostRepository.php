<?php

namespace AppBundle\Repository;
use Doctrine\ORM\EntityRepository;

/**
 * PostRepository
 */
class PostRepository extends EntityRepository
{
    public function fetchResults($search){
        $query = $this->createQueryBuilder('u')
            ->select('u')
            ->where('u.content LIKE :search')
            ->orWhere('u.category LIKE :search')
            ->orWhere('u.subcategory LIKE :search')
            ->setParameter('search', '%'.$search.'%')
            ->getQuery()
        ;

        return $query->getResult();
    }
}
