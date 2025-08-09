<?php
namespace App\Controller;

use App\Entity\ContentItem;
use App\Enum\ContentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function __invoke(EntityManagerInterface $em, Request $req)
    {
        $query = $req->query->get('q');
        $type = $req->query->get('type');
        $sort = $req->query->get('sort', 'popularity');
        $page = $req->query->getInt('page', 1);
        $perPage = 10;

        $qb = $em->getRepository(ContentItem::class)->createQueryBuilder('c');

        if ($query) {
            $qb->andWhere('LOWER(c.title) LIKE :query OR JSON_CONTAINS(LOWER(c.tags), :tag) = 1')
               ->setParameter('query', '%' . strtolower($query) . '%')
               ->setParameter('tag', json_encode(strtolower($query)));
        }

        if ($type && in_array($type, ['video', 'text'])) {
            $qb->andWhere('c.type = :type')
               ->setParameter('type', $type === 'video' ? ContentType::VIDEO : ContentType::TEXT);
        }

        if ($sort === 'relevance' && $query) {
            // For relevance sorting, we could implement more complex scoring here
            $qb->orderBy('c.finalScore * 
                CASE 
                    WHEN LOWER(c.title) LIKE :titleQuery THEN 2 
                    WHEN JSON_CONTAINS(LOWER(c.tags), :tagQuery) = 1 THEN 1.5 
                    ELSE 1 
                END', 'DESC')
               ->setParameter('titleQuery', '%' . strtolower($query) . '%')
               ->setParameter('tagQuery', json_encode(strtolower($query)));
        } else {
            $qb->orderBy('c.finalScore', 'DESC');
        }

        $offset = ($page - 1) * $perPage;
        $items = $qb->setFirstResult($offset)
                   ->setMaxResults($perPage)
                   ->getQuery()
                   ->getResult();

        return $this->render('dashboard/index.html.twig', [
            'items' => $items,
            'page' => $page,
            'query' => $query,
            'type' => $type,
            'sort' => $sort
        ]);
    }
}
