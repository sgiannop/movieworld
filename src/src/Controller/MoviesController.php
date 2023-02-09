<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Vote;
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
        $response = new Response($twig->render('movies/header.html.twig', [
            'user' => $this->getUser()
        ]));
        //$response->setSharedMaxAge(3600);
        return $response;
    }

    #[Route('/', name: 'movies')]
    public function index(Request $request, MovieRepository $movieRepository, Environment $twig) : Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        if($this->getUser() != null) {
            $username = $request->query->get('username');
            if($username == $this->getUser()->getUserIdentifier()) {
                $user = $this->getUser();
            } else {
                $user = null;
            }
        } else {
            $user = null;
        }
        //$column = isset($_POST['column']) ? $_POST['column'] : 'createdAt';
        $column = $request->query->get('column') ? $request->query->get('column') : 'createdAt';
        $dir = $request->query->get('dir') ? $request->query->get('dir') : 'ASC';
        $filter = $request->query->get('filter');
        
        $paginator = $movieRepository->getMoviePaginator($offset, $column, $dir, $user);

        $response = new Response($twig->render('movies/index.html.twig', [      
            'movies' => $paginator,
            'previous' => $offset - MovieRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + MovieRepository::PAGINATOR_PER_PAGE),
            'user' => $this->getUser()
        ]));
        // $response->setSharedMaxAge(3600);
        return $response;
    }
    

    #[Route('/movies/{id}', name: 'movie')]
    public function show(Request $request, 
                        MovieRepository $movieRepository, VoteRepository $voteRepository, 
                        #[Autowire('%photo_dir%')] string $photoDir, Movie $movie = null): Response
    {
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
         
            // $movie = $movieRepository->findBy(['id' => basename($request->getUri())], ['createdAt' => 'DESC']);
            $movie = $form->getData();
            $movie->setOwner($this->getUser());
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
            'movie_form' => $form,
            'user' => $this->getUser()
        ]);
    }

    #[Route('/movielike', name: 'movielike')]
    public function addlike(Request $request, MovieRepository $movieRepository, VoteRepository $voteRepository) : Response
    {

        $liked = $request->query->get('liked');
        $id = $request->query->get('id');

        $movie = $movieRepository->findBy(['id' => $id], ['createdAt' => 'DESC'])[0];

        $vote = new Vote();
        if($liked == true) {
            $vote->setIsLiked(true);
            $likes = $movie->getLikes();
            if($likes == null) $likes = 0;
            $movie->setLikes(++$likes);
        } else {
            $vote->setIsLiked(false);
            $hates = $movie->getHates();
            if($hates == null) $hates = 0;
            $movie->setHates(++$hates);
        }
        
        $vote->setMovie($movie);
        $vote->setVoter($this->getUser());
        $voteRepository->save($vote, true);
        $movieRepository->save($movie, true);

        $this->addFlash('notice', 'Your changes were saved!');
        return $this->redirectToRoute('movies');

    }

    #[Route('/movies/sortLikes/{id}', name: 'sortLikes')]
    public function sortLikes($id) {
        return null;
    }

}
    