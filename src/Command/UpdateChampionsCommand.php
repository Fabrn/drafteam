<?php

namespace App\Command;

use App\Bridge\LeagueOfLegends\DataDragon\DataDragonService;
use App\Entity\Champion;
use App\Entity\ChampionData;
use App\Repository\ChampionDataRepository;
use App\Repository\ChampionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsCommand(name: 'app:champions:update', description: 'Updates list of champions and their data in the database.')]
final readonly class UpdateChampionsCommand
{
    public function __construct(
        private DataDragonService $dataDragonService,
        private CacheInterface $cache,
        private ChampionRepository $championRepository,
        private ChampionDataRepository $championDataRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    public function __invoke(SymfonyStyle $io, #[Option] string $lang = 'en_US'): int
    {
        $latestVersion = $this->cache->get('league_of_legends.data_dragon.last_version', function (ItemInterface $item) {
            $item->expiresAfter(86400);

            return $this->dataDragonService->getLatestVersion();
        });

        $io->note('Dernière version du jeu : ' . $latestVersion);

        try {
            $champions = $this->dataDragonService->getChampions($latestVersion, $lang);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $progressBar = $io->createProgressBar(\count($champions));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->start();

        foreach ($champions as $champion) {
            $progressBar->advance();

            $existingChampion = $this->championRepository->findOneBy([
                'lolId' => $champion->id,
                'lolKey' => $champion->key,
            ]);

            if (null === $existingChampion) {
                $progressBar->setMessage(\sprintf('%s - Creating champion for language "%s".', $champion->id, $lang));

                $this->entityManager->persist(
                    $c = new Champion(
                        lolId: $champion->id,
                        lolKey: $champion->key,
                        imageFull: $champion->image->full,
                        imageSprite: $champion->image->sprite,
                        imageX: $champion->image->x,
                        imageY: $champion->image->y,
                        imageWidth: $champion->image->w,
                        imageHeight: $champion->image->h,
                    ),
                );

                $this->entityManager->persist(new ChampionData(
                    champion: $c,
                    language: $lang,
                    name: $champion->name,
                    title: $champion->title,
                ));

                $this->entityManager->flush();

                continue;
            }

            $existingLanguage = $this->championDataRepository->findOneBy([
                'champion' => $existingChampion,
                'language' => $lang,
            ]);

            if (null === $existingLanguage) {
                $progressBar->setMessage(\sprintf('%s - Creating data for language "%s".', $champion->id, $lang));

                $this->entityManager->persist(new ChampionData(
                    champion: $existingChampion,
                    language: $lang,
                    name: $champion->name,
                    title: $champion->title,
                ));

                $this->entityManager->flush();

                continue;
            }

            $progressBar->setMessage(\sprintf('%s - Updating data for language "%s".', $champion->id, $lang));

            $existingLanguage->name = $champion->name;
            $existingLanguage->title = $champion->title;

            $this->entityManager->flush();
        }

        $progressBar->finish();

        $io->newLine(2);

        $io->success('Champions updated successfully.');

        return Command::SUCCESS;
    }
}
