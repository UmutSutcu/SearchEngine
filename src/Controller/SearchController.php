<?php
namespace App\Controller;

use App\Entity\ContentItem;
use App\Service\ScoreCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class SearchController extends AbstractController
{
    #[Route('/api/search', methods: ['GET'])]
    public function search(
        Request $req,
        EntityManagerInterface $em,
        ScoreCalculator $sc,
        CacheInterface $cache
    ): JsonResponse {
        $q     = trim((string)$req->query->get('query', ''));
        $type  = (string)$req->query->get('type', ''); // ""|"video"|"text"
        $sort  = (string)$req->query->get('sort', 'popularity'); // "popularity"|"relevance"
        $page  = max(1, (int)$req->query->get('page', 1));
        $pp    = min(50, max(1, (int)$req->query->get('per_page', 10)));

        $key = sprintf('search:%s:%s:%s:%d:%d', md5($q), $type, $sort, $page, $pp);

        $payload = $cache->get($key, function (ItemInterface $item) use ($em, $q, $type, $sort, $page, $pp, $sc) {
            $item->expiresAfter(60); // 60 sn cache

            $qb = $em->getRepository(ContentItem::class)->createQueryBuilder('c');

            if ($q !== '') {
                $qb->andWhere('LOWER(c.title) LIKE :q OR CAST(c.tags AS text) LIKE :q')
                   ->setParameter('q', '%'.mb_strtolower($q).'%');
            }

            if (in_array($type, ['video','text'], true)) {
                $qb->andWhere('c.type = :t')->setParameter('t', $type);
            }

            $qb->addOrderBy('c.finalScore', 'DESC')
               ->setFirstResult(($page-1)*$pp)
               ->setMaxResults($pp);

            /** @var ContentItem[] $items */
            $items = $qb->getQuery()->getResult();

            if ($sort === 'relevance' && $q !== '') {
                usort($items, fn($a, $b) =>
                    ($b->getFinalScore() * $sc->relevanceBoost($b, $q))
                  <=> ($a->getFinalScore() * $sc->relevanceBoost($a, $q))
                );
            }

            $data = array_map(function(ContentItem $c) {
                return [
                    'id'    => $c->getId(),
                    'title' => $c->getTitle(),
                    'type'  => $c->getType()->value,
                    'score' => [
                        'base'        => $c->getBaseScore(),
                        'freshness'   => $c->getFreshnessScore(),
                        'interaction' => $c->getInteractionScore(),
                        'final'       => $c->getFinalScore(),
                    ],
                    'metrics' => [
                        'views'        => $c->getViews(),
                        'likes'        => $c->getLikes(),
                        'reading_time' => $c->getReadingTime(),
                        'reactions'    => $c->getReactions(),
                        'duration'     => $c->getDuration(),
                    ],
                    'published_at' => $c->getPublishedAt()->format(DATE_ATOM),
                    'tags'         => $c->getTags(),
                    'provider'     => $c->getProvider(),
                ];
            }, $items);

            return [
                'data'       => $data,
                'pagination' => ['page'=>$page, 'per_page'=>$pp, 'count'=>count($data)],
            ];
        });

        return new JsonResponse($payload);
    }
}
