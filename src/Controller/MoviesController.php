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
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Twig\Environment;
class MoviesController extends AbstractController
{
    
    #[Route('/movie_header', name: 'movie_header')]
    public function movieHeader(MovieRepository $movieRepository, Environment $twig): Response
    {
        return new Response($twig->render('movies/header.html.twig', [
            'movies' => $movieRepository->findAll(),
        ]));
    }

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
    public function show(Request $request, Movie $movie, 
                        MovieRepository $movieRepository, VoteRepository $voteRepository, 
                        ManagerRegistry $doctrine, #[Autowire('%photo_dir%')] string $photoDir): Response
    {
        $form = $this->createForm(MovieFormType::class, $movie);
        $entityManager = $doctrine->getManager();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
         
            $movie = $movieRepository->findBy(['id' => basename($request->getUri())], ['createdAt' => 'DESC']);
            $movie = $form->getData();

            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                try {
                    $photo->move($photoDir, $filename);
                } catch (FileException $e) {
                // unable to upload the photo, give up
                }
                $movie->setPhotoPath($filename);
                $movieRepository->save($movie, true);
                $this->addFlash('notice', 'Your changes were saved!');
                return $this->redirectToRoute('movies');
            }

        }

        return $this->render('movies/show.html.twig', [
            'movie' => $movie,
            'votes' => $voteRepository->findBy(['movie' => $movie], ['createdAt' => 'DESC']),
            'movie_form' => $form
        ]);
    }
}
    