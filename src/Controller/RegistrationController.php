<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManager;
use App\Form\RegistrationFormType;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\ProfessionnelRegistrationType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, ManagerRegistry $manager): Response
    {   
        $user = new Utilisateur();
        $em=$manager->getManager();
       
        $type = $request->query->get('type', 'particulier'); 

        if ($type === 'professionnel') {
            $form = $this->createForm(ProfessionnelRegistrationType::class, $user);

            $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));
            $user->setRoles(['ROLE_PROFESSIONNEL']);
            $user->setStatus(true);
            try{      
                $em->persist($user);
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('danger', 'Cet email est déjà utilisé.');
                return $this->redirectToRoute('app_register');
            }

            return $this->redirectToRoute('app_login');
        }
        } else {
            $form = $this->createForm(RegistrationFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));
            $user->setRoles(['ROLE_USER']);
            $user->setStatus(true);
          try{      
            $em->persist($user);
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            $this->addFlash('danger', 'Cet email est déjà utilisé.');
            return $this->redirectToRoute('app_register');
        }
            return $this->redirectToRoute('app_login');
        }
        }

        
        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
            'type' => $type, 
        ]);
    }
    
}
