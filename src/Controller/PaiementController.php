<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\PaymeeService;  // Assurez-vous d'avoir ce service Paymee

final class PaiementController extends AbstractController
{
    #[Route('/paiement/{commandeId}', name: 'paiement')]
    public function index(int $commandeId, CommandeRepository $commandeRepository): Response
    {
        // Récupérer la commande à partir de son ID
        $commande = $commandeRepository->find($commandeId);

        if (!$commande) {
            // Si la commande n'existe pas, afficher un message d'erreur
            $this->addFlash('error', 'Commande non trouvée.');
            return $this->redirectToRoute('app_cart');
        }

        // Passer les informations de la commande à la vue
        return $this->render('paiement/paiement.html.twig', [
            'commande' => $commande
        ]);
    }

    #[Route('/traiter-paiement/{commandeId}', name: 'traiter_paiement', methods: ['POST'])]
    public function traiterPaiement(int $commandeId, Request $request, CommandeRepository $commandeRepository, EntityManagerInterface $entityManager, PaymeeService $paymeeService): Response
    {
        // Récupérer la commande
        $commande = $commandeRepository->find($commandeId);

        if (!$commande) {
            $this->addFlash('error', 'Commande non trouvée.');
            return $this->redirectToRoute('app_cart');
        }

        // Récupérer le mode de paiement depuis le formulaire
        $modePaiement = $request->request->get('mode_paiement');

        if ($modePaiement === 'livraison') {
            $commande->setStatut('Payé à la livraison');
        } elseif ($modePaiement === 'visa') {
            $commande->setStatut('Payé par VISA');
        }

        // Sauvegarder les modifications dans la base de données
        $entityManager->persist($commande);
        $entityManager->flush();

        if ($modePaiement === 'visa') {
            // Si le mode de paiement est 'visa', redirige vers Paymee pour paiement
            $amount = $commande->getPrixTotal();  // Assurez-vous de récupérer le montant de la commande
            $ligneCommande = $commande->getLigneCommandes()->first();

            if ($ligneCommande) {
                $customer = $ligneCommande->getUserC();
                $firstName = $customer->getPrenom();  // ou getNomUser() selon vos besoins
                $lastName  = $customer->getNomUser();
                $email     = $customer->getEmail();
                $phone     = $customer->getNumTel();
            }
            
            // Définir l'identifiant de la commande
            $orderId = $commande->getId();
            $paymentData = $paymeeService->createPayment($amount, $firstName, $lastName, $email, $phone, $orderId);
        

            if (isset($paymentData['data']['payment_url'])) {
                $paymentUrl = $paymentData['data']['payment_url'];
                return $this->redirect($paymentUrl);
            } else {
                // Gérer l'erreur, par exemple afficher un message ou journaliser la réponse
                $this->addFlash('error', 'Erreur lors de la création du paiement. Veuillez réessayer.');
                return $this->redirectToRoute('paiement');
            }
            

            // Rediriger l'utilisateur vers l'URL de paiement
            return $this->redirect($paymentUrl);
        }

        // Si le paiement est effectué par livraison ou un autre mode
        $this->addFlash('success', 'Votre paiement a été effectué avec succès.');

        // Rediriger l'utilisateur vers la page articles
        return $this->redirectToRoute('app_article');
    }

    // Méthode pour gérer le webhook Paymee
    #[Route('/webhook-paymee', name: 'paymee_webhook', methods: ['POST'])]
    public function paymeeWebhook(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // Vérifier l'intégrité de la réponse avec le check_sum
        $checkSum = md5($data['token'] . $data['payment_status'] . $_ENV['PAYMEE_API_KEY']);

        if ($data['check_sum'] === $checkSum) {
            if ($data['payment_status']) {
                // Paiement réussi, traite la commande ici
                $this->addFlash('success', 'Paiement réussi');
            } else {
                // Paiement échoué, gère l'erreur
                $this->addFlash('error', 'Paiement échoué');
            }
        }

        return new Response('Webhook reçu');
    }
}
