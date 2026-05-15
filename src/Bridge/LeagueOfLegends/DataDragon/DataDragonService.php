<?php

namespace App\Bridge\LeagueOfLegends\DataDragon;

use App\Bridge\LeagueOfLegends\DataDragon\Dto\Output\ChampionOutput;
use App\Bridge\LeagueOfLegends\DataDragon\Exception\DataDragonRequestException;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class DataDragonService
{
    public function __construct(
        #[Target('datadragon.client')]
        private HttpClientInterface $httpClient,
        private SerializerInterface $serializer,
    ) {}

    /**
     * @return list<non-empty-string>
     * @throws DataDragonRequestException
     */
    public function getVersions(): array
    {
        try {
            $response = $this->httpClient->request(method: Request::METHOD_GET, url: '/api/versions.json');

            $content = $response->getContent();
        } catch (ExceptionInterface $e) {
            throw new DataDragonRequestException($e->getMessage(), previous: $e);
        }

        return \json_decode($content, true);
    }

    /**
     * @return non-empty-string
     * @throws DataDragonRequestException
     */
    public function getLatestVersion(): string
    {
        return $this->getVersions()[0];
    }

    /**
     * @return list<ChampionOutput>
     * @throws DataDragonRequestException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getChampions(string $version, string $language = 'en_US'): array
    {
        try {
            $response = $this->httpClient->request(method: Request::METHOD_GET, url: \sprintf(
                '/cdn/%s/data/%s/champion.json',
                $version,
                $language,
            ));

            $content = $response->getContent();
        } catch (ExceptionInterface $e) {
            throw new DataDragonRequestException($e->getMessage(), previous: $e);
        }

        return $this->serializer->deserialize($content, ChampionOutput::class . '[]', 'json', [
            UnwrappingDenormalizer::UNWRAP_PATH => '[data]',
        ]);
    }
}
