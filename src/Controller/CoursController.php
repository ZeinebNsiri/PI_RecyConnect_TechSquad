<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\Rating;
use App\Form\ChatType;
use App\Form\CoursType;
use App\Form\RatingType;
use App\ServiceChat\ChatClient;
use App\Repository\CoursRepository;
use App\Repository\RatingRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CoursController extends AbstractController
{
    #[Route('/cours', name: 'app_allcours')]
    public function getAllcours(CoursRepository $repo, Request $request,PaginatorInterface $paginator): Response
    {
        $selectedCategory = $request->query->get('category'); // Get the selected category from URL
        $categories       = $repo->findUniqueCategories();    // Fetch all unique categories

        if ($selectedCategory) {
            $cours = $repo->findByCategory($selectedCategory); // Filter courses if category is selected
        } else {
            $cours = $repo->findAll(); 
        }
        $pagination = $paginator->paginate(
            $cours,
            $request->query->getInt('page', 1), 
            5
        );
        return $this->render('cours/index.html.twig', [
            'cours'           => $pagination,
            // On préfixe array_column par un backslash
            'categories'      => \array_column($categories, 'nomCategorie'),
            'selectedCategory'=> $selectedCategory,
        ]);
    }

    // ---------------------------------------------
    // add
    // ---------------------------------------------
    #[Route('/add-cours', name: 'appcours_add')]
    public function add(
        Request $request,
        ManagerRegistry $manager,
        #[Autowire('%photo_dir%')] string $photoDir,
        #[Autowire('%video_dir%')] string $videoDir
    ): Response {
        $cours = new Cours();
        $form  = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image
            $photoFile = $form->get('imageCours')->getData();
            if ($photoFile) {
                $fileName = $photoFile->getClientOriginalName();
                $photoFile->move($photoDir, $fileName);
                $cours->setImageCours($fileName);
            }

            // Gestion de la vidéo
            $videoFile = $form->get('video')->getData();
            if ($videoFile) {
                $fileName = $videoFile->getClientOriginalName();
                $videoFile->move($videoDir, $fileName);
                $cours->setVideo($fileName);
            }

            $em = $manager->getManager();
            $em->persist($cours);
            $em->flush();

            $this->addFlash('success', 'Le cours a été ajouté avec succès !');
            return $this->redirectToRoute('app_allcours');
        }

        return $this->render('cours/add-cours.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ---------------------------------------------
    // edit
    // ---------------------------------------------
    #[Route('/updatecours/{id}', name: 'app_editcours')]
    public function edit(
        Request $request,
        ManagerRegistry $manager,
        CoursRepository $repo,
        #[Autowire('%photo_dir%')] string $photoDir,
        #[Autowire('%video_dir%')] string $videoDir,
        int $id
    ): Response {
        $cours = $repo->find($id);
        if (!$cours) {
            throw $this->createNotFoundException('Cours non trouvé');
        }

        $oldImage = $cours->getImageCours();
        $form     = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Image
            $photoFile = $form->get('imageCours')->getData();
            if ($photoFile) {
                $fileName = $photoFile->getClientOriginalName();
                $photoFile->move($photoDir, $fileName);
                $cours->setImageCours($fileName);

                // Suppression de l'ancienne image
                if ($oldImage && file_exists($photoDir.'/'.$oldImage)) {
                    unlink($photoDir.'/'.$oldImage);
                }
            } else {
                $cours->setImageCours($oldImage);
            }

            // Vidéo
            $videoFile = $form->get('video')->getData();
            if ($videoFile) {
                $videoName = $videoFile->getClientOriginalName();
                $videoFile->move($videoDir, $videoName);
                $cours->setVideo($videoName);
            }

            $em = $manager->getManager();
            $em->flush();

            $this->addFlash('success', 'Le cours a été modifié avec succès.');
            return $this->redirectToRoute('app_allcours');
        }

        return $this->render('cours/edit-cours.html.twig', [
            'form'  => $form->createView(),
            'cours' => $cours
        ]);
    }

    // ---------------------------------------------
    // delete
    // ---------------------------------------------
    #[Route('/deletecours/{id}', name: 'app_deletecours')]
    public function deleteCours(ManagerRegistry $manager, CoursRepository $repo, $id )
    {
        $em   = $manager->getManager();
        $cour = $repo->find($id);

        $em->remove($cour);
        $em->flush();

        return $this->redirectToRoute('app_allcours');
    }

    // ---------------------------------------------
    // showWorkshops
  

#[Route('/workshops', name: 'app_workshops', methods: ['GET', 'POST'])]
public function showWorkshops(
    CoursRepository $coursRepository,
    RatingRepository $ratingRepository,
    Request $request,
    ChatClient $chatClient
): Response {
    // Choose base template
    $template = $this->getUser() ? 'basecnx.html.twig' : 'base.html.twig';

    // 1) Fetch categories, filters, workshops, etc. (same as your code)
    $categories       = $coursRepository->findUniqueCategories();
    $selectedCategory = $request->query->get('category');
    $searchTitle      = $request->query->get('searchTitle');
    $videoFilter      = $request->query->get('video');
    $workshops        = $coursRepository->findByAllFilters($selectedCategory, $searchTitle, $videoFilter);

    // 2) Add user rating to each workshop (same as your code)
    $user = $this->getUser();
    foreach ($workshops as $w) {
        if ($user) {
            $existingRating = $ratingRepository->findOneBy([
                'cours' => $w,
                'user'  => $user
            ]);
            $w->userRating = $existingRating ? $existingRating->getNote() : 0;
        } else {
            $w->userRating = 0;
        }
    }

    // 3) Handle chat form
    $chatForm = $this->createForm(ChatType::class);
    $chatForm->handleRequest($request);

    // Retrieve conversation from session (default to empty array)
    $session = $request->getSession();
    $conversation = $session->get('conversation', []);

    if ($chatForm->isSubmitted() && $chatForm->isValid()) {
        // a) Get user’s prompt from the form
        $prompt = $chatForm->get('prompt')->getData();

        // b) Add user’s message to conversation
        $conversation[] = [
            'sender' => 'user',
            'text'   => $prompt,
        ];

        // c) Call your ChatClient to get an AI response
        try {
            $answer = $chatClient->getAnswer($prompt);
        } catch (\Exception $e) {
            $answer = "Error processing your request: " . $e->getMessage();
        }

        // d) Add bot’s response to conversation
        $conversation[] = [
            'sender' => 'bot',
            'text'   => $answer,
        ];

        // e) Store the updated conversation back in session
        $session->set('conversation', $conversation);
    }

    // 4) Render template, passing the entire conversation
    return $this->render('cours/courscnx_front.html.twig', [
        'workshops'        => $workshops,
        'categories'       => array_column($categories, 'nomCategorie'),
        'selectedCategory' => $selectedCategory,
        'searchTitle'      => $searchTitle,
        'videoFilter'      => $videoFilter,
        'template'         => $template,

        'chat_form'        => $chatForm->createView(),
        'conversation'     => $conversation, // <--- important
    ]);
}

    // ---------------------------------------------
    // showWorkshopDetails
    // ---------------------------------------------
    #[Route('/workshops/{id}', name: 'appworkshop_details')]
    public function showWorkshopDetails(
        int $id,
        CoursRepository $coursRepository,
        RatingRepository $ratingRepository,
        ManagerRegistry $managerRegistry,
        Request $request
    ) {
        // Vérifier le rôle
        if (!$this->isGranted('ROLE_USER') && !$this->isGranted('ROLE_PROFESSIONNEL')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $workshop = $coursRepository->find($id);
        if (!$workshop) {
            throw $this->createNotFoundException('Workshop inexistant.');
        }

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier si l'utilisateur a déjà noté ce cours
        $existingRating = $ratingRepository->findOneBy([
            'cours' => $workshop,
            'user'  => $user,
        ]);

        if ($existingRating) {
            $rating = $existingRating;
        } else {
            $rating = new Rating();
            $rating->setUser($user);
            $rating->setCours($workshop);
            $rating->setDateRate(new \DateTime());
        }

        // Créer le formulaire
        $form = $this->createForm(RatingType::class, $rating);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $managerRegistry->getManager();
            $em->persist($rating);
            $em->flush();

            $this->addFlash('success', 'Merci pour votre note !');
            // Redirection pour éviter la resoumission du formulaire
            return $this->redirectToRoute('appworkshop_details', ['id' => $id]);
        }

        return $this->render('cours/detailscours_front.html.twig', [
            'workshop'   => $workshop,
            'ratingForm' => $form->createView(),
        ]);
    }

    // ---------------------------------------------
    // showWorkshopsguest
    // ---------------------------------------------
    #[Route('/workshopsg', name: 'app_workshopsg')]
    public function showWorkshopsguest(CoursRepository $coursRepository, Request $request): Response
    {
        $categories      = $coursRepository->findUniqueCategories();
        $selectedCategory= $request->query->get('category');

        if ($selectedCategory) {
            $workshops = $coursRepository->findByCategory($selectedCategory);
        } else {
            $workshops = $coursRepository->findAll();
        }

        return $this->render('cours/coursguest_front.html.twig', [
            'workshops'        => $workshops,
            // Correction array_column
            'categories'       => \array_column($categories, 'nomCategorie'),
            'selectedCategory' => $selectedCategory,
        ]);
    }

    // ---------------------------------------------
    // dashboardCours
    // ---------------------------------------------
    #[Route('/dashboard/cours', name: 'app_cours_dashboard')]
    public function dashboardCours(
        CoursRepository $coursRepository,
        RatingRepository $ratingRepository
    ): Response {
        $statsCategory           = $coursRepository->countByCategory();
        $statsRatingSumByCategory= $ratingRepository->getRatingSumByCategory();
        $averageByWorkshop       = $ratingRepository->getAverageRatingByWorkshop();

        return $this->render('cours/dashboard-cours.html.twig', [
            'statsCategory'           => $statsCategory,
            'statsRatingSumByCategory'=> $statsRatingSumByCategory,
            'averageByWorkshop'       => $averageByWorkshop,
        ]);
    }
}
