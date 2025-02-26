<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    /**
     * Démarre le processus d'authentification Google
     *
     * @Route("/connect/google", name="connect_google_start")
     */
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectAction(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry->getClient('google')->redirect(
            ['email', 'profile'], // Google Scopes
            []
        );
    }

    /**
     * Callback de Google après authentification
     *
     * @Route("/connect/google/check", name="connect_google_check")
     */
    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(Request $request)
    {
        if (!$this->getUser()) {
            return $this->json(['status' => false, 'message' => "Utilisateur non trouvé !"]);
        }

        return $this->redirectToRoute('homecnx');
    }
}
