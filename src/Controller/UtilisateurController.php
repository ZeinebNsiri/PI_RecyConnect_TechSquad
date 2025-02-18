<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UpdateUserType;
use App\Form\UpdateUserproType;
use App\Repository\UtilisateurRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class UtilisateurController extends AbstractController
{
    #[Route('/Liste/utilisateurs/{type?}', name: 'app_Listeutilisateur')]
    public function Listeutilisateur(?string $type,UtilisateurRepository $repository): Response
    {   
        
        if ($type == 'particuliers') {
            $users = $repository->findByRole('ROLE_USER');
        } elseif ($type == 'professionnels') {
            $users = $repository->findByRole('ROLE_PROFESSIONNEL');
        } else {
            $users = $repository->findusers();
        }
    

        return $this->render('utilisateur/index.html.twig', [
            'users' => $users,
        ]);
    }
    #[Route('/activer/user/{id}', name: 'app_Activer')]
    public function activer($id,UtilisateurRepository $repository,ManagerRegistry $manager): Response
    {   
        $user = $repository -> find($id);
        $em=$manager->getManager();
        if ($user->isStatus() == true) {
            $this->addFlash('danger', 'Ce compte est déja activé.');
        } else {
            $user-> setStatus(true);
            $em->flush();
            $this->addFlash('danger', 'Compte activé avec succès.');
        } 
    

        return $this->redirectToRoute('app_Listeutilisateur');
    }

    #[Route('/desactiver/user/{id}', name: 'app_Desactiver')]
    public function deactiver($id,UtilisateurRepository $repository,ManagerRegistry $manager): Response
    {   
        $user = $repository -> find($id);
        $em=$manager->getManager();
        if ($user->isStatus() == false) {
            $this->addFlash('danger', 'Ce compte est déja désactivé.');
        } else {
            $user-> setStatus(false);
            $em->flush();
            $this->addFlash('danger', 'Compte désactivé avec succès.');
        } 
    

        return $this->redirectToRoute('app_Listeutilisateur');
    }

    #[Route('/profileadmin/{id}', name: 'app_profileadmin')]
    public function profileadmin(Request $req,ManagerRegistry $manager,UtilisateurRepository $repository,$id, SluggerInterface $slugger,
    #[Autowire('%kernel.project_dir%/public/uploads/profile_dir')] string $brochuresDirectory
    ): Response
    {   
        $em=$manager->getManager();
        $user = $repository -> find($id);


        


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
                       $em->refresh($user);
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
                    {$em->refresh($user);
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
}


