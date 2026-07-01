<?php

namespace App\Repository;

use App\Entity\ChampionData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChampionData>
 */
class ChampionDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChampionData::class);
    }

    /**
     * @return list<ChampionData>
     */
    public function findContainingName(string $name, string $locale = 'en_US'): array
    {
        return ($qb = $this->createQueryBuilder('champion_data'))
            ->where($qb->expr()->like($qb->expr()->lower('champion_data.name'), ':name'))
            ->andWhere($qb->expr()->eq('champion_data.language', ':locale'))
            ->orderBy('champion_data.name', Order::Ascending->value)
            ->setParameter('name', '%' . \strtolower($name) . '%')
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult();
    }
}
