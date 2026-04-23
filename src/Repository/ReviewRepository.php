<?php

namespace App\Repository;

use App\DTO\CompanyStats;
use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @return CompanyStats[]
     */
    public function findCompanyStats(?string $search = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.companyName, COUNT(r.id) AS reviewCount, AVG(r.rating) AS avgRating')
            ->groupBy('r.companyName')
            ->orderBy('avgRating', 'DESC');

        if (null !== $search && '' !== $search) {
            $qb->andWhere('r.companyName LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return array_map(
            fn(array $row) => new CompanyStats(
                companyName: $row['companyName'],
                reviewCount: (int) $row['reviewCount'],
                avgRating: (float) $row['avgRating'],
            ),
            $qb->getQuery()->getArrayResult()
        );
    }
}
