<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReviewController extends AbstractController
{
    #[Route('/', name: 'review_index')]
    public function index(Request $request, EntityManagerInterface $em, ReviewRepository $reviewRepository, PaginatorInterface $paginator): Response
    {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($review);
            $em->flush();

            $this->addFlash('success', 'Köszönjük a véleményed!');

            return $this->redirectToRoute('review_index');
        }

        $reviews = $paginator->paginate(
            $reviewRepository->createQueryBuilder('r')->orderBy('r.createdAt', 'DESC'),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('review/index.html.twig', [
            'form' => $form,
            'reviews' => $reviews,
        ]);
    }

    #[Route('/companies', name: 'company_index')]
    public function companies(Request $request, ReviewRepository $reviewRepository, PaginatorInterface $paginator): Response
    {
        $search = $request->query->getString('search');

        $companies = $paginator->paginate(
            $reviewRepository->findCompanyStats($search ?: null),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('review/companies.html.twig', [
            'companies' => $companies,
            'search' => $search,
        ]);
    }

    #[Route('/review/{id}', name: 'review_show', requirements: ['id' => '\d+'])]
    public function show(Review $review): Response
    {
        return $this->render('review/show.html.twig', [
            'review' => $review,
        ]);
    }
}
