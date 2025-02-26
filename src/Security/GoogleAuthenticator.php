<?php

namespace App\Security;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class GoogleAuthenticator extends AbstractAuthenticator
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $em;
    private RouterInterface $router;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
    }

    public function supports(Request $request): bool
    {
        return $request->getPathInfo() === '/connect/google/check' && $request->isMethod('GET');
    }

    public function authenticate(Request $request): Passport
    {
        // Récupérer le client Google
        $client = $this->getGoogleClient();
        
        // Obtenir le token d'accès Google
        $accessToken = $client->getAccessToken();
        
        /** @var GoogleUser $googleUser */
        $googleUser = $client->fetchUserFromToken($accessToken);
        $email = $googleUser->getEmail();

        // Vérifier si l'utilisateur existe déjà
        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        // Si l'utilisateur n'existe pas, le créer et l'enregistrer dans la base de données
        if (!$user) {
            $user = new Utilisateur();
            $user->setEmail($email);
            $user->setNomUser($googleUser->getName());
            $user->setRoles(['ROLE_USER']); // Par défaut, un nouvel utilisateur est un utilisateur normal
            $user->setStatus(true); // Statut actif par défaut

            // Sauvegarde dans la base de données
            $this->em->persist($user);
            $this->em->flush();
        }

        return new Passport(
            new UserBadge($email, function () use ($user) {
                return $user;
            }),
            new CustomCredentials(fn ($credentials, UserInterface $user) => true, $accessToken)
        );
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?RedirectResponse
    {
        return new RedirectResponse('/homecnx'); // Rediriger vers le tableau de bord après connexion réussie
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        return new RedirectResponse('/login'); // Rediriger vers la connexion en cas d'échec
    }

    private function getGoogleClient(): OAuth2Client
    {
        return $this->clientRegistry->getClient('google');
    }
}
