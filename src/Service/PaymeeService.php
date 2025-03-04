<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaymeeService
{
    private $client;
    private $apiKey;
    private $sandboxUrl;
    private $liveUrl;
    private $returnUrl;
    private $cancelUrl;
    private $webhookUrl;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiKey = $_ENV['PAYMEE_API_KEY'];   // Clé API Paymee
        $this->sandboxUrl = 'https://sandbox.paymee.tn/api/v2/payments/create';  // URL Sandbox
        $this->liveUrl = 'https://app.paymee.tn/api/v2/payments/create';  // URL Production
        $this->returnUrl = $_ENV['PAYMEE_RETURN_URL'];  // URL de retour après paiement
        $this->cancelUrl = $_ENV['PAYMEE_CANCEL_URL'];  // URL si paiement annulé
        $this->webhookUrl = $_ENV['PAYMEE_WEBHOOK_URL'];  // URL du webhook pour vérifier le statut du paiement
    }

    public function createPayment(float $amount, string $firstName, string $lastName, string $email, string $phone, string $orderId)
    {
        // On utilise l'URL de sandbox ou de production
        $url = $this->sandboxUrl;

        // Données de la requête
        $data = [
            'amount' => $amount,
            'note' => "Order #$orderId",
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'return_url' => $this->returnUrl,
            'cancel_url' => $this->cancelUrl,
            'webhook_url' => $this->webhookUrl,
            'order_id' => $orderId
        ];

        // En-têtes pour l'authentification
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Token ' . $this->apiKey
        ];

        // Envoi de la requête POST à Paymee
        $response = $this->client->request('POST', $url, [
            'headers' => $headers,
            'json' => $data
        ]);

        return $response->toArray();  // Retourner les données de la réponse
    }
}
