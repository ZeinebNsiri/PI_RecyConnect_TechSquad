<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\PaymeeService;

final class PaiementController extends AbstractController
{
    #[Route('/paiement/{commandeId}', name: 'paiement')]
    public function index(int $commandeId, CommandeRepository $commandeRepository): Response
    {
        $commande = $commandeRepository->find($commandeId);

        if (!$commande) {
            $this->addFlash('error', 'Commande non trouvée.');
            return $this->redirectToRoute('app_cart');
        }

        return $this->render('paiement/paiement.html.twig', [
            'commande' => $commande
        ]);
    }

    #[Route('/traiter-paiement/{commandeId}', name: 'traiter_paiement', methods: ['POST'])]
    public function traiterPaiement(
        int $commandeId,
        Request $request,
        CommandeRepository $commandeRepository,
        EntityManagerInterface $entityManager,
        PaymeeService $paymeeService
    ): Response
    {
        $commande = $commandeRepository->find($commandeId);
    
        if (!$commande) {
            $this->addFlash('error', 'Commande non trouvée.');
            return $this->redirectToRoute('app_cart');
        }
    
        // Vérifier si la commande est déjà payée
        if (in_array($commande->getStatut(), ['Payé', 'Payé par VISA'])) {
            $this->addFlash('warning', 'Cette commande a déjà été payée.');
            return $this->redirectToRoute('app_article');
        }
    
        $modePaiement = $request->request->get('mode_paiement');
    
        if ($modePaiement === 'livraison') {
            $commande->setStatut('Payé à la livraison');
        } elseif ($modePaiement === 'visa') {
            $commande->setStatut('Payé par VISA');  // Mise à jour du statut en "Payé par VISA"
        } else {
            $this->addFlash('error', 'Mode de paiement invalide.');
            return $this->redirectToRoute('paiement', ['commandeId' => $commandeId]);
        }
    
        $entityManager->persist($commande);
        $entityManager->flush();
    
        if ($modePaiement === 'visa') {
            $paymentData = $this->generatePaymentData($commande, $paymeeService);
    
            if (isset($paymentData['data']['payment_url'])) {
                return $this->redirect($paymentData['data']['payment_url']);
            } else {
                $this->addFlash('error', 'Erreur lors de la création du paiement.');
                dump($paymentData); // Pour déboguer les erreurs
                return $this->redirectToRoute('paiement', ['commandeId' => $commandeId]);
            }
        }
    
        $this->addFlash('success', 'Votre paiement a été enregistré.');
        return $this->redirectToRoute('app_article');
    }
    
    #[Route('/webhook-paymee', name: 'paymee_webhook', methods: ['POST'])]
    public function paymeeWebhook(
        Request $request,
        CommandeRepository $commandeRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $data = json_decode($request->getContent(), true);

        // Vérification du checksum
        $checkSum = md5($data['token'] . $data['payment_status'] . $_ENV['PAYMEE_API_KEY']);
        if ($data['check_sum'] === $checkSum) {
            $commande = $commandeRepository->find($data['order_id']);

            if ($commande) {
                if ($data['payment_status'] === 'SUCCESS') {
                    $commande->setStatut('Payé');
                    $this->addFlash('success', 'Paiement confirmé.');
                } else {
                    $commande->setStatut('Échec du paiement');
                    $this->addFlash('error', 'Paiement échoué.');
                }

                $entityManager->persist($commande);
                $entityManager->flush();
            }
        }

        return new Response('Webhook traité.');
    }

    #[Route('/retour-paiement/{commandeId}', name: 'retour_paiement')]
    public function retourPaiement(
        int $commandeId,
        CommandeRepository $commandeRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $commande = $commandeRepository->find($commandeId);

        if (!$commande) {
            $this->addFlash('error', 'Commande introuvable.');
            return $this->redirectToRoute('app_cart');
        }

        if ($commande->getStatut() !== 'Payé') {
            $commande->setStatut('Payé');
            $entityManager->persist($commande);
            $entityManager->flush();
            $this->addFlash('success', 'Votre paiement a été confirmé.');
        }

        return $this->redirectToRoute('app_article');
    }

    #[Route('/generate-payment-url/{commandeId}', name: 'generate_payment_url', methods: ['POST'])]
    public function generatePaymentUrl(
        int $commandeId,
        CommandeRepository $commandeRepository,
        PaymeeService $paymeeService
    ): Response
    {
        $commande = $commandeRepository->find($commandeId);

        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée.'], 400);
        }

        $paymentData = $this->generatePaymentData($commande, $paymeeService);

        if (isset($paymentData['data']['payment_url'])) {
            return $this->json(['paymentUrl' => $paymentData['data']['payment_url']]);
        }

        return $this->json(['error' => 'Erreur lors de la création du paiement.'], 500);
    }

    private function generatePaymentData($commande, PaymeeService $paymeeService)
    {
        $amount = $commande->getPrixTotal();
        $ligneCommande = $commande->getLigneCommandes()->first();

        if ($ligneCommande) {
            $customer = $ligneCommande->getUserC();
            $firstName = $customer->getPrenom();
            $lastName = $customer->getNomUser();
            $email = $customer->getEmail();
            $phone = $customer->getNumTel();
        }

        return $paymeeService->createPayment($amount, $firstName, $lastName, $email, $phone, $commande->getId());
    }
}
