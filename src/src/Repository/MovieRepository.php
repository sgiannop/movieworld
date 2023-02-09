<?php

namespace App\Repository;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Movie>
 *
 * @method Movie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movie[]    findAll()
 * @method Movie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 2;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    public function getMoviePaginator(int $offset, $column = 'createdAt', $dir = 'ASC',  $filter = null): Paginator
    {
        $query = $this->createQueryBuilder('c')->orderBy('c.' . $column, $dir);
        if($filter != null) 
        {
            $query->andWhere('c.owner = :owner')->setParameter('owner', $filter);
        }

        $query->setMaxResults(self::PAGINATOR_PER_PAGE)->setFirstResult($offset)->getQuery();

         return new Paginator($query);
    }

    public function findAll(): array
    {
        return $this->findBy([], [ 'createdAt' => 'ASC' ]);
    }

    public function sortBy($column, $dir): array
    {
        return $this->findBy([], [ $column => $dir ]);
    }

    public function save(Movie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Movie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   /**
    * @return Movie[] Returns an array of Movie objects
    */
   public function findByExampleField($value): array
   {
       return $this->createQueryBuilder('m')
           ->andWhere('m.exampleField = :val')
           ->setParameter('val', $value)
           ->orderBy('m.id', 'ASC')
           ->setMaxResults(10)
           ->getQuery()
           ->getResult()
       ;
   }

//    public function findOneBySomeField($value): ?Movie
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
