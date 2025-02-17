<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Form\CoursType;
use App\Repository\CoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CoursController extends AbstractController
{
    #[Route('/cours', name: 'app_allcours')]
    public function getAllcours(CoursRepository $repo)
    {
        $cours= $repo->findAll();
        return $this->render('cours/index.html.twig',
        ['cours'=>$cours]);
    }

    #[Route('/add-cours', name: 'appcours_add')]
    public function add(
        Request $request,
        ManagerRegistry $manager,
        #[Autowire('%photo_dir%')] string $photoDir,
        #[Autowire('%video_dir%')] string $videoDir
    ): Response {
        $cours = new Cours();

        // On génère le formulaire
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        // Quand on soumet le formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du fichier image
            $photoFile = $form->get('imageCours')->getData();
            if ($photoFile) {
                $fileName = $photoFile->getClientOriginalName();
                // Chemin d’upload local éventuel (non utilisé ci-dessous)
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/images';

                $photoFile->move(
                    $photoDir,  // Utilisation du chemin injecté par l’attribut Autowire
                    $fileName
                );
                $cours->setImageCours($fileName);
            }

            // Récupération du fichier vidéo
            $videoFile = $form->get('video')->getData();
            if ($videoFile) {
                $fileName = $videoFile->getClientOriginalName();
                // Chemin d’upload local éventuel (non utilisé ci-dessous)
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/videos';

                $videoFile->move(
                    $videoDir,  // Utilisation du chemin injecté par l’attribut Autowire
                    $fileName
                );
                $cours->setVideo($fileName);
            }

            // Sauvegarde en base
            $em = $manager->getManager();
            $em->persist($cours);
            $em->flush();

            // Message flash
            $this->addFlash('success', 'Le cours a été ajouté avec succès !');

            // Redirection
            return $this->redirectToRoute('app_allcours');
        }

        return $this->render('cours/add-cours.html.twig', [
            'form' => $form->createView(),
        ]);
    }
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
    
        // Keep track of the old image name
        $oldImage = $cours->getImageCours();
    
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // PHOTO
            $photoFile = $form->get('imageCours')->getData();
            if ($photoFile) {
                // The user uploaded a new image
                $fileName = $photoFile->getClientOriginalName();
                $photoFile->move($photoDir, $fileName);
                $cours->setImageCours($fileName);
    
                // Optionally delete the old file from disk if you want:
                if ($oldImage && file_exists($photoDir.'/'.$oldImage)) {
                    unlink($photoDir.'/'.$oldImage);
                }
            } else {
                // No new file => restore the old one
                $cours->setImageCours($oldImage);
            }
    
            // VIDEO
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



//                     #[Route('/admin/events/edit/{id}', name: 'edit_event', methods: ['GET', 'POST'])]
// public function edit(Request $request, Evenement $event, EntityManagerInterface $entityManager): Response
// {
//     $oldImage = $event->getImageEvent();

//     $form = $this->createForm(EventType::class, $event);
//     $form->handleRequest($request);

//     if ($form->isSubmitted() && $form->isValid()) {
//         $imageFile = $form->get('imageEvent')->getData();

//         if ($imageFile) {
//             $newFilename = $imageFile->getClientOriginalName();

//             $imageFile->move(
//                 $this->getParameter('photo_dir'),
//                 $newFilename
//             );

//             $event->setImageEvent($newFilename);

//             if ($oldImage && file_exists($this->getParameter('photo_dir').'/'.$oldImage)) {
//                 unlink($this->getParameter('photo_dir').'/'.$oldImage);
//             }
//         } else {
//             $event->setImageEvent($oldImage);
//         }

//         $entityManager->flush();

//         $this->addFlash('success', 'L\'événement a été mis à jour avec succès.');
//         return $this->redirectToRoute('admin_events');
//     }

                    //delete
                    #[Route('/deletecours/{id}', name: 'app_deletecours')]
                    public function deleteCours(ManagerRegistry $manager, CoursRepository $repo, $id )
                    {
                        $em= $manager->getManager();
                        $cour = $repo->find($id);
                    
                        $em->remove($cour)  ;
                        $em->flush();
                
                        return $this->redirectToRoute('app_allcours');
                    }

                    #[Route('/workshops', name: 'app_workshops')]
                    public function showWorkshops(CoursRepository $coursRepository, Request $request): Response
                    {
                        // On récupère toutes les catégories distinctes
                        $categories = $coursRepository->findUniqueCategories();
                        
                        // Récupération de la catégorie choisie via ?category=...
                        $selectedCategory = $request->query->get('category');
                    
                        // Si une catégorie est choisie, on filtre, sinon on affiche tout
                        if ($selectedCategory) {
                            $workshops = $coursRepository->findByCategory($selectedCategory);
                        } else {
                            $workshops = $coursRepository->findAll();
                        }
                    
                        // On extrait seulement le champ 'nomCategorie' pour construire un simple tableau de chaînes
                        return $this->render('cours/courscnx_front.html.twig', [
                            'workshops'        => $workshops,
                            'categories'       => array_column($categories, 'nomCategorie'), 
                            'selectedCategory' => $selectedCategory,
                        ]);
                    }

                    #[Route('/workshops/{id}', name: 'appworkshop_details')]
                    public function showWorkshopDetails(int $id, CoursRepository $coursRepository): Response
                    {
                        // Fetch the workshop by its ID
                        $workshop = $coursRepository->find($id);

                        // If the workshop is not found, throw a 404 error
                        if (!$workshop) {
                            throw $this->createNotFoundException('Workshop not found');
                        }

                        return $this->render('cours/detailscours_front.html.twig', [
                            'workshop' => $workshop,
                        ]);
                    }

                    

}
