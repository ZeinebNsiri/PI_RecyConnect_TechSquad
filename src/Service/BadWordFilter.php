<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BadWordFilter
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function filterText(string $text): string
    {
        $url = 'https://www.purgomalum.com/service/json';

        $response = $this->client->request('GET', $url, [
            'query' => ['text' => $text]
        ]);

        $data = $response->toArray();

        return $data['result'] ?? $text; // Retourne le texte filtré
    }
}
