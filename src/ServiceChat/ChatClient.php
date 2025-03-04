<?php

declare(strict_types=1);

namespace App\ServiceChat;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    public function getAnswer(string $prompt): string
    {
        try {
            $apiUrl = $this->parameterBag->get('chat_gpt_api_url');
            $apiKey = $this->parameterBag->get('chat_gpt_api_key');

            $response = $this->httpClient->request(
                Request::METHOD_POST,
                $apiUrl,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'inputs' => "<s>[INST] $prompt [/INST]",
                        'parameters' => [
                            'max_new_tokens' => 200,
                            'temperature' => 0.7,
                            'return_full_text' => false
                        ]
                    ],
                    'timeout' => 60
                ]
            );

            $statusCode = $response->getStatusCode();
            
            if ($statusCode !== 200) {
                return "API Error: HTTP $statusCode";
            }

            $responseData = $response->toArray(false);
            
            // Handle different response formats
            if (isset($responseData['error'])) {
                if (str_contains($responseData['error'], 'loading')) {
                    return "Model is loading. Please try again in 20 seconds.";
                }
                return "Error: " . $responseData['error'];
            }

            if (isset($responseData[0]['generated_text'])) {
                return trim($responseData[0]['generated_text']);
            }

            return 'No response from AI model';

        } catch (\Exception $e) {
            error_log("HF API Error: " . $e->getMessage());
            return "Service unavailable. Please try again later.";
        }
    }
}