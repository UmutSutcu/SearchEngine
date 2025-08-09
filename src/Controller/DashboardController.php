<?php
namespace App\Controller;

use App\Entity\ContentItem;
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
        $items = $em->getRepository(ContentItem::class)->createQueryBuilder('c')
            ->orderBy('c.finalScore', 'DESC')
            ->setMaxResults(50)->getQuery()->getResult();

        return $this->render('dashboard/index.html.twig', ['items' => $items]);
    }
}
