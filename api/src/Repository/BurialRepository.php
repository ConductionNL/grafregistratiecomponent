<?php

namespace App\Repository;

use App\Entity\Burial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Burial|null find($id, $lockMode = null, $lockVersion = null)
 * @method Burial|null findOneBy(array $criteria, array $orderBy = null)
 * @method Burial[]    findAll()
 * @method Burial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BurialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }
}
