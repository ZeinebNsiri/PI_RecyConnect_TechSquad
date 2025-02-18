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
    public function getAllcours(CoursRepository $repo, Request $request): Response
    {
    $selectedCategory = $request->query->get('category'); // Get the selected category from URL
    $categories = $repo->findUniqueCategories(); // Fetch all unique categories

    if ($selectedCategory) {
        $cours = $repo->findByCategory($selectedCategory); // Filter courses if category is selected
    } else {
        $cours = $repo->findAll(); // Otherwise, get all courses
    }

    return $this->render('cours/index.html.twig', [
        'cours' => $cours,
        'categories' => array_column($categories, 'nomCategorie'), // Extract category names
        'selectedCategory' => $selectedCategory,
    ]);
    }


    //add
    #[Route('/add-cours', name: 'appcours_add')]
    public function add(
        Request $request,
        ManagerRegistry $manager,
        #[Autowire('%photo_dir%')] string $photoDir,
        #[Autowire('%video_dir%')] string $videoDir
    ): Response {
        $cours = new Cours();

       
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {
          
            $photoFile = $form->get('imageCours')->getData();
            if ($photoFile) {
                $fileName = $photoFile->getClientOriginalName();
               
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/images';

                $photoFile->move(
                    $photoDir, 
                    $fileName
                );
                $cours->setImageCours($fileName);
            }

           
            $videoFile = $form->get('video')->getData();
            if ($videoFile) {
                $fileName = $videoFile->getClientOriginalName();
               
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/videos';

                $videoFile->move(
                    $videoDir,  
                    $fileName
                );
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

    //edit
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
    
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
           
            $photoFile = $form->get('imageCours')->getData();
            if ($photoFile) {
                
                $fileName = $photoFile->getClientOriginalName();
                $photoFile->move($photoDir, $fileName);
                $cours->setImageCours($fileName);
    
                
                if ($oldImage && file_exists($photoDir.'/'.$oldImage)) {
                    unlink($photoDir.'/'.$oldImage);
                }
            } else {
               
                $cours->setImageCours($oldImage);
            }
    
            
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
                        
                        $categories = $coursRepository->findUniqueCategories();
                        
                      
                        $selectedCategory = $request->query->get('category');
                    
                       
                        if ($selectedCategory) {
                            $workshops = $coursRepository->findByCategory($selectedCategory);
                        } else {
                            $workshops = $coursRepository->findAll();
                        }
                    
                       
                        return $this->render('cours/courscnx_front.html.twig', [
                            'workshops'        => $workshops,
                            'categories'       => array_column($categories, 'nomCategorie'), 
                            'selectedCategory' => $selectedCategory,
                        ]);
                    }

                    #[Route('/workshops/{id}', name: 'appworkshop_details')]
                    public function showWorkshopDetails(int $id, CoursRepository $coursRepository): Response
                    {
                       
                        $workshop = $coursRepository->find($id);

                      
                        if (!$workshop) {
                            throw $this->createNotFoundException('Workshop n existe pas');
                        }

                        return $this->render('cours/detailscours_front.html.twig', [
                            'workshop' => $workshop,
                        ]);
                    }


                
                    #[Route('/workshopsg', name: 'app_workshopsg')]
                    public function showWorkshopsguest(CoursRepository $coursRepository, Request $request): Response
                    {
                       
                        $categories = $coursRepository->findUniqueCategories();
                        
                       
                        $selectedCategory = $request->query->get('category');
                    
                       
                        if ($selectedCategory) {
                            $workshops = $coursRepository->findByCategory($selectedCategory);
                        } else {
                            $workshops = $coursRepository->findAll();
                        }
                    
                       
                        return $this->render('cours/coursguest_front.html.twig', [
                            'workshops'        => $workshops,
                            'categories'       => array_column($categories, 'nomCategorie'), 
                            'selectedCategory' => $selectedCategory,
                        ]);
                    }

                    

}
