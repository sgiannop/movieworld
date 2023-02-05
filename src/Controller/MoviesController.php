<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use App\Repository\VoteRepository;
use App\Form\MovieFormType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class MoviesController extends AbstractController
{
    #[Route('/', name: 'movies')]
    public function index(Request $request, MovieRepository $movieRepository): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $movieRepository->getMoviePaginator($offset);

        return $this->render('movies/index.html.twig', [      
            // 'movies' => $movieRepository->findAll(),
            'movies' => $paginator,
            'previous' => $offset - MovieRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + MovieRepository::PAGINATOR_PER_PAGE),
        ]);
    }

    #[Route('/movies/{id}', name: 'movie')]
    public function show(Movie $movie, VoteRepository $voteRepository): Response
    {
        $form = $this->createForm(MovieFormType::class, $movie);

        return $this->render('movies/show.html.twig', [
            'movie' => $movie,
            'votes' => $voteRepository->findBy(['movie' => $movie], ['createdAt' => 'DESC']),
            'movie_form' => $form
        ]);
    }
}
    