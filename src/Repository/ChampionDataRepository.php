<?php

namespace App\Repository;

use App\Entity\ChampionData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<ChampionData>
 */
class ChampionDataRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        #[Autowire(param: 'app.locales')]
        private readonly string $locales,
    ) {
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

    /**
     * @return list<ChampionData>
     */
    public function findByRequestLanguage(Request $request): array
    {
        $locales = \explode('|', $this->locales);
        $data = $this->findBy(
            criteria: ['language' => $request->getPreferredLanguage($locales)],
            orderBy: ['name' => 'ASC'],
        );

        if ([] === $data) {
            $data = $this->findBy(
                criteria: ['language' => 'en_US'],
                orderBy: ['name' => 'ASC'],
            );
        }

        return $data;
    }
}
