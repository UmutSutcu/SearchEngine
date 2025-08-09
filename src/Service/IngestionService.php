<?php
namespace App\Service;

use App\Entity\ContentItem;
use App\Entity\Tag;
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
                $limiter = $this->jsonLimiter->create('json-provider');
            } else {
                $limiter = $this->xmlLimiter->create('xml-provider');
            }

            if (!$limiter->consume(1)->isAccepted()) {
                continue;
            }
            
            // Pre-load existing tags to avoid duplicates
            $existingTags = [];
            $tagRepo = $this->em->getRepository(Tag::class);
            foreach ($tagRepo->findAll() as $tag) {
                $existingTags[$tag->getName()] = $tag;
            }

            foreach ($p->fetch() as $n) {
                $item = $this->upsert($p->name(), $n, $existingTags);
                $this->sc->compute($item);
                $this->em->persist($item);
                $count++;
            }
            $this->em->flush();
        }
        return $count;
    }

    private function upsert(string $provider, array $n, array &$existingTags): ContentItem
    {
        $repo = $this->em->getRepository(ContentItem::class);
        $item = $repo->findOneBy(['provider'=>$provider, 'externalId'=>$n['external_id']]) ?? new ContentItem();

        $isNew = $item->getId() === null;

        $item->setProvider($provider);
        $item->setExternalId($n['external_id']);
        $item->setTitle($n['title']);
        $item->setType($n['type']==='video' ? ContentType::VIDEO : ContentType::TEXT);
        
        // Clear existing tags and add new ones
        $item->clearTags();
        foreach ($n['tags'] ?? [] as $tagName) {
            if (!isset($existingTags[$tagName])) {
                $tag = new Tag();
                $tag->setName($tagName);
                $this->em->persist($tag);
                $existingTags[$tagName] = $tag;
            }
            $item->addTag($existingTags[$tagName]);
        }
        
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
