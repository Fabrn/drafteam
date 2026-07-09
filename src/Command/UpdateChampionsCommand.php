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
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'app:champions:update', description: 'Updates list of champions and their data in the database.')]
final readonly class UpdateChampionsCommand
{
    public function __construct(
        private DataDragonService $dataDragonService,
        private ChampionRepository $championRepository,
        private ChampionDataRepository $championDataRepository,
        private EntityManagerInterface $entityManager,
        #[Autowire(param: 'kernel.project_dir')]
        private string $projectDir,
    ) {}

    public function __invoke(SymfonyStyle $io, #[Option] string $lang = 'en_US'): int
    {
        $fs = new Filesystem();
        $latestVersion = $this->dataDragonService->getLatestVersion();

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

                // Champion's square image
                $squareUrl = \sprintf(
                    'https://ddragon.leagueoflegends.com/cdn/%s/img/champion/%s.png',
                    $latestVersion,
                    $champion->id,
                );

                $dbSquarePath = '/images/champions/squares/' . $champion->key . '.png';
                $squarePath = $this->projectDir . '/public' . $dbSquarePath;

                $fs->dumpFile($squarePath, \file_get_contents($squareUrl));

                // Champion's splash art image
                $splashUrl = \sprintf('https://ddragon.leagueoflegends.com/cdn/img/champion/splash/%s_0.jpg', $champion->id);

                $dbSplashPath = '/images/champions/splash_arts/' . $champion->key . '.png';
                $splashPath = $this->projectDir . '/public' . $dbSplashPath;

                $fs->dumpFile($splashPath, \file_get_contents($splashUrl));

                $this->entityManager->persist(
                    $c = new Champion(
                        lolId: $champion->id,
                        lolKey: $champion->key,
                        imageSquarePath: $dbSquarePath,
                        imageSplashPath: $dbSplashPath,
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
