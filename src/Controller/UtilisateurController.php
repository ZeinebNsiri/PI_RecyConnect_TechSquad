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
    #[Route('/Liste/utilisateurs', name: 'app_Listeutilisateur')]
    public function Listeutilisateur(UtilisateurRepository $repository): Response
    {
        $users=$repository->findusers();
        return $this->render('utilisateur/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/profile/{id}', name: 'app_profile')]
    public function profile(Request $req,ManagerRegistry $manager,UtilisateurRepository $repository,$id, SluggerInterface $slugger,
    #[Autowire('%kernel.project_dir%/public/uploads/profile_dir')] string $brochuresDirectory
    ): Response
    {   
        $em=$manager->getManager();
        $user = $repository -> find($id);

        




        if(in_array('ROLE_PROFESSIONNEL',$this->getUser()->getRoles(),true)){
        
            $form = $this->createForm(UpdateUserproType::class,$user);
            $form->handleRequest($req);
                if($form->isSubmitted()&& $form->isValid())

                    {   $photo_profil = $form->get('photo_profil')->getData();

                        // this condition is needed because the 'brochure' field is not required
                        // so the PDF file must be processed only when a file is uploaded
                        if ($photo_profil) {
                            $originalFilename = pathinfo($photo_profil->getClientOriginalName(), PATHINFO_FILENAME);
                            // this is needed to safely include the file name as part of the URL
                            $safeFilename = $slugger->slug($originalFilename);
                            $newFilename = $safeFilename.'-'.uniqid().'.'.$photo_profil->guessExtension();

                            // Move the file to the directory where brochures are stored
                            try {
                                $photo_profil->move($brochuresDirectory, $newFilename);
                            } catch (FileException $e) {
                                // ... handle exception if something happens during file upload
                            }

                            // updates the 'brochureFilename' property to store the PDF file name
                            // instead of its contents
                            $user-> setPhotoProfil($newFilename);
                        }



                    $em->flush();

                    return $this->redirectToRoute('app_profile', ['id' => $id]);

                    }

            return $this->render('utilisateur/profile.html.twig', [
            'form'=>$form->createView()
            ]);
        }
    
       
        
       
        $form = $this->createForm(UpdateUserType::class,$user);
        $form->handleRequest($req);
        if($form->isSubmitted()&& $form->isValid())

                    {
                        $photo_profil = $form->get('photo_profil')->getData();

                        // this condition is needed because the 'brochure' field is not required
                        // so the PDF file must be processed only when a file is uploaded
                        if ($photo_profil) {
                            $originalFilename = pathinfo($photo_profil->getClientOriginalName(), PATHINFO_FILENAME);
                            // this is needed to safely include the file name as part of the URL
                            $safeFilename = $slugger->slug($originalFilename);
                            $newFilename = $safeFilename.'-'.uniqid().'.'.$photo_profil->guessExtension();

                            // Move the file to the directory where brochures are stored
                            try {
                                $photo_profil->move($brochuresDirectory, $newFilename);
                            } catch (FileException $e) {
                                // ... handle exception if something happens during file upload
                            }

                            // updates the 'brochureFilename' property to store the PDF file name
                            // instead of its contents
                            $user-> setPhotoProfil($newFilename);
                        }


                    $em->persist($user);
                    $em->flush();

                    return $this->redirectToRoute('app_profile', ['id' => $id]);

                    }

        
        return $this->render('utilisateur/profile.html.twig', [
            'form'=>$form->createView()
        ]);
    }
}
