<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UpdateUserType;
use App\Form\UpdateUserproType;
use App\Repository\UtilisateurRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class UtilisateurController extends AbstractController
{
    #[Route('/Liste/utilisateurs/{type?}', name: 'app_Listeutilisateur')]
        public function Listeutilisateur(?string $type, UtilisateurRepository $repository, PaginatorInterface $paginator, Request $request): Response
        {   
            // Récupérer les critères de recherche depuis la requête
            $email = $request->query->get('email');
            
            $numTel = $request->query->get('numTel');
            
            $role = $request->query->get('role');

            // Vérifier si une recherche est en cours
            if ($email || $numTel ||  $role  !== null) {
                // Recherche avancée avec plusieurs critères
                $users = $repository->searchUsers($email, $numTel,  $role);
            } else {
                // Sinon, appliquer le filtre existant basé sur {type}
                if ($type == 'particuliers') {
                    $users = $repository->findByRole('ROLE_USER');
                } elseif ($type == 'professionnels') {
                    $users = $repository->findByRole('ROLE_PROFESSIONNEL');
                } elseif ($type == 'true') {
                    $users = $repository->findBystatus(true);
                } elseif ($type == 'false') {
                    $users = $repository->findBystatus(false);
                } else {
                    $users = $repository->findusers();
                }
            }

            // Pagination
            $pagination = $paginator->paginate(
                $users,
                $request->query->getInt('page', 1),
                5
            );

            return $this->render('utilisateur/index.html.twig', [
                'users' => $pagination,
            ]);
        }

    #[Route('/activer/user/{id}', name: 'app_Activer')]
    public function activer($id,UtilisateurRepository $repository,ManagerRegistry $manager): Response
    {   
        $user = $repository->find($id);
            $em = $manager->getManager();

            if ($user) {
                $user->setStatus(true); // ou false dans 'deactiver'
                $em->persist($user); // Ajouté pour éviter l'oubli de l'entité
                $em->flush();
                
                return $this->json(['success' => true, 'redirectUrl' => $this->generateUrl('app_Listeutilisateur')]);
            }

            return $this->json(['success' => false, 'message' => 'Utilisateur non trouvé']);

    }

    #[Route('/desactiver/user/{id}', name: 'app_Desactiver')]
    public function deactiver($id,UtilisateurRepository $repository,ManagerRegistry $manager): Response
    {   
                $user = $repository->find($id);
        $em = $manager->getManager();

        if ($user) {
            $user->setStatus(false); // ou false dans 'deactiver'
            $em->persist($user); // Ajouté pour éviter l'oubli de l'entité
            $em->flush();
            
            return $this->json(['success' => true, 'redirectUrl' => $this->generateUrl('app_Listeutilisateur')]);
        }

        return $this->json(['success' => false, 'message' => 'Utilisateur non trouvé']);

    }

    #[Route('/profileadmin/{id}', name: 'app_profileadmin')]
    public function profileadmin(Request $req,ManagerRegistry $manager,UtilisateurRepository $repository,$id, SluggerInterface $slugger,
    #[Autowire('%kernel.project_dir%/public/uploads/profile_dir')] string $brochuresDirectory
    ): Response
    {   
        $em=$manager->getManager();
        $user = $repository -> find($id);
        $userOriginal = clone $user;

        


        // modifier le profile

        $form = $this->createForm(UpdateUserType::class,$user);
        $form->handleRequest($req);
        if($form->isSubmitted()&& $form->isValid())

                    {
                        $photo_profil = $form->get('photo_profil')->getData();

                        
                        if ($photo_profil) {
                            $originalFilename = pathinfo($photo_profil->getClientOriginalName(), PATHINFO_FILENAME);
                            
                            $safeFilename = $slugger->slug($originalFilename);
                            $newFilename = $safeFilename.'-'.uniqid().'.'.$photo_profil->guessExtension();

                            
                            try {
                                $photo_profil->move($brochuresDirectory, $newFilename);
                            } catch (FileException $e) {
                               
                            }

                            
                            $user-> setPhotoProfil($newFilename);
                        }


                    $em->persist($user);
                    $em->flush();

                    return $this->redirectToRoute('app_profileadmin', ['id' => $id]);

                }else{
                    $user=$userOriginal;
                }

        
        return $this->render('utilisateur/profile.html.twig', [
            'form'=>$form->createView(),
            'user'=>$user
        ]);
    }

    #[Route('/profile/{id}', name: 'app_profile')]
    public function profile(Request $req,ManagerRegistry $manager,UtilisateurRepository $repository,$id, SluggerInterface $slugger,
    #[Autowire('%kernel.project_dir%/public/uploads/profile_dir')] string $brochuresDirectory
    ): Response
    {   
        $em=$manager->getManager();
        $user = $repository -> find($id);
        $userOriginal = clone $user;

        


        // modifier le profile
        if(in_array('ROLE_PROFESSIONNEL',$this->getUser()->getRoles(),true)){
            
        
            $form = $this->createForm(UpdateUserproType::class,$user);
            $form->handleRequest($req);
                if($form->isSubmitted())
                    {if($form->isValid()){
                       
                        $photo_profil = $form->get('photo_profil')->getData();

                        
                        if ($photo_profil) {
                            $originalFilename = pathinfo($photo_profil->getClientOriginalName(), PATHINFO_FILENAME);
                            
                            $safeFilename = $slugger->slug($originalFilename);
                            $newFilename = $safeFilename.'-'.uniqid().'.'.$photo_profil->guessExtension();

                            
                            try {
                                $photo_profil->move($brochuresDirectory, $newFilename);
                            } catch (FileException $e) {
                                
                            }

                            
                            $user-> setPhotoProfil($newFilename);
                        }



                        $em->flush();
                        
                        return $this->redirectToRoute('app_profile', ['id' => $id]);
                    }else{
                        $user=$userOriginal;
                    }
                    }

           
        }
    
       
        
        if(in_array('ROLE_USER',$this->getUser()->getRoles(),true)){
        $form = $this->createForm(UpdateUserType::class,$user);
        $form->handleRequest($req);
        if($form->isSubmitted()&& $form->isValid())
            {if($form->isValid())
                    {
                        $photo_profil = $form->get('photo_profil')->getData();

                        
                        if ($photo_profil) {
                            $originalFilename = pathinfo($photo_profil->getClientOriginalName(), PATHINFO_FILENAME);
                            
                            $safeFilename = $slugger->slug($originalFilename);
                            $newFilename = $safeFilename.'-'.uniqid().'.'.$photo_profil->guessExtension();

                            
                            try {
                                $photo_profil->move($brochuresDirectory, $newFilename);
                            } catch (FileException $e) {
                               
                            }

                            
                            $user-> setPhotoProfil($newFilename);
                        }


                    //$em->persist($user);
                    $em->flush();

                    return $this->redirectToRoute('app_profile', ['id' => $id]);
                }else{
                    $user=$userOriginal;
                }
                    }
                }
        
        return $this->render('utilisateur/profileUser.html.twig', [
            'form'=>$form->createView(),
            'user'=>$user
        ]);
    }
    #[Route('/statistiques/user', name: 'app_statistiques_utlisateurs')]
    public function index(): Response
    {
        return $this->render('utilisateur/satistiquesUser.html.twig');
    }

    #[Route('/statistiques/data', name: 'app_statistiques_data')]
    public function getStats(UtilisateurRepository $repository): JsonResponse
    {
        // Récupérer tous les utilisateurs avec ROLE_USER ou ROLE_PROFESSIONNEL
        $users = $repository->findAll();

        // Initialisation des compteurs
        $rolesCounts = [
            'Professionnels' => 0,
            'Particuliers' => 0
        ];
        $statusCounts = [
            'Activés' => 0,
            'Désactivés' => 0
        ];

        // Parcourir les utilisateurs pour compter les rôles et les statuts
        foreach ($users as $user) {
            $roles = $user->getRoles(); // Tableau de rôles JSON

            if (in_array('ROLE_PROFESSIONNEL', $roles)) {
                $rolesCounts['Professionnels']++;

                // Vérifier le statut uniquement pour ROLE_PROFESSIONNEL
                if ($user->isStatus()) {
                    $statusCounts['Activés']++;
                } else {
                    $statusCounts['Désactivés']++;
                }
            } elseif (in_array('ROLE_USER', $roles)) {
                $rolesCounts['Particuliers']++;

                // Vérifier le statut uniquement pour ROLE_USER
                if ($user->isStatus()) {
                    $statusCounts['Activés']++;
                } else {
                    $statusCounts['Désactivés']++;
                }
            }
        }

        return new JsonResponse([
            'roles' => $rolesCounts,
            'status' => $statusCounts
        ]);
    }
}



