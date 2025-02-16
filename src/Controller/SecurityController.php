<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;



class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        $errorMessageKey = null;
        
    
        if ($error) {
            
            $errorMessageKey = $error->getMessageKey(); 
            if ($error instanceof BadCredentialsException) {
                $errorMessageKey = 'Email ou mot de passe incorrect. Veuillez réessayer.';
            } 
        }
    
       

        return $this->render('security/login.html.twig', [ 'error' => $error, 'error_message_key' => $errorMessageKey, ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): RedirectResponse
    {
         return $this->redirectToRoute('app_login');
    }
}
