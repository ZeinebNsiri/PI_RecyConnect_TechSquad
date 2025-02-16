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
                    //edit
                    #[Route('/updatecours{id}', name: 'app_editcours')]
                    public function edit(
                        Request $request,
                        ManagerRegistry $manager,
                        CoursRepository $repo,
                        #[Autowire('%photo_dir%')] string $photoDir,
                        #[Autowire('%video_dir%')] string $videoDir,
                        $id
                    ): Response {
                        $cours = $repo->find($id);

                        if (!$cours) {
                            throw $this->createNotFoundException('Cours non trouvé');
                        }

                        $form = $this->createForm(CoursType::class, $cours);
                        $form->handleRequest($request);

                        if ($form->isSubmitted() && $form->isValid()) {
                            // Handling Image Upload
                            $photoFile = $form->get('imageCours')->getData();
                            if ($photoFile) {
                                $fileName = $photoFile->getClientOriginalName();
                                $photoFile->move($photoDir, $fileName);
                                $cours->setImageCours($fileName);
                            }

                            // Handling Video Upload
                            $videoFile = $form->get('video')->getData();
                            if ($videoFile) {
                                $fileName = $videoFile->getClientOriginalName();
                                $videoFile->move($videoDir, $fileName);
                                $cours->setVideo($fileName);
                            }

                            $em = $manager->getManager();
                            $em->flush();

                            $this->addFlash('success', 'Le cours a été modifié avec succès.');

                            return $this->redirectToRoute('app_allcours');
                        }

                        return $this->render('cours/edit-cours.html.twig', [
                            'form' => $form->createView(),
                            'cours' => $cours
                        ]);
                    }

                    #[Route('/deletecours/{id}', name: 'app_deletecours')]
                    public function deleteCours(ManagerRegistry $manager, CoursRepository $repo, $id )
                    {
                        $em= $manager->getManager();
                        $cour = $repo->find($id);
                    
                        $em->remove($cour)  ;
                        $em->flush();
                
                        return $this->redirectToRoute('app_allcours');
                    }

}
