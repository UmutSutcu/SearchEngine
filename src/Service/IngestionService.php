<?php
namespace App\Service;

use App\Entity\ContentItem;
use App\Enum\ContentType;
use App\Provider\ProviderClientInterface;
use App\Service\ScoreCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;


final class IngestionService
{
    /**
     * @param iterable<ProviderClientInterface> $providers
     */
    public function __construct(
        private iterable $providers,
        private EntityManagerInterface $em,
        private ScoreCalculator $sc,
        private RateLimiterFactory $jsonLimiter,
        private RateLimiterFactory $xmlLimiter,
    ) {}

    public function ingestAll(): int
    {
        $count = 0;
        foreach ($this->providers as $p) {
            if ($p->name() === 'json') {
                // ESKİ: $this->jsonLimiter->create('json-provider');
                $limiter = $this->jsonLimiter->create('json-provider');
            } else {
                // ESKİ: $this->xmlLimiter->create('xml-provider');
                $limiter = $this->xmlLimiter->create('xml-provider');
            }

            if (!$limiter->consume(1)->isAccepted()) {
                continue;
            }

            foreach ($p->fetch() as $n) {
                $item = $this->upsert($p->name(), $n);
                $this->sc->compute($item);
                $this->em->persist($item);
                $count++;
            }
            $this->em->flush();
        }
        return $count;
    }

    private function upsert(string $provider, array $n): ContentItem
    {
        $repo = $this->em->getRepository(ContentItem::class);
        $item = $repo->findOneBy(['provider'=>$provider, 'externalId'=>$n['external_id']]) ?? new ContentItem();

        $isNew = $item->getId() === null;

        $item->setProvider($provider);
        $item->setExternalId($n['external_id']);
        $item->setTitle($n['title']);
        $item->setType($n['type']==='video' ? ContentType::VIDEO : ContentType::TEXT);
        $item->setTags($n['tags'] ?? []);
        $item->setViews($n['views'] ?? null);
        $item->setLikes($n['likes'] ?? null);
        $item->setReadingTime($n['reading_time'] ?? null);
        $item->setReactions($n['reactions'] ?? null);
        $item->setDuration($n['duration'] ?? null);
        $item->setPublishedAt(new \DateTimeImmutable($n['published_at']));
        $item->setRaw($n['raw'] ?? null);

        if ($isNew) {
            $item->setCreatedAt(new \DateTimeImmutable());
        }
        $item->setUpdatedAt(new \DateTimeImmutable());

        return $item;
    }
}
