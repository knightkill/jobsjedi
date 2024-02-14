<?php

namespace App\Libraries\Services;

use App\Libraries\BoardsContract;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class JustRemote implements BoardsContract
{
    private string $url;
    private Client $client;

    public function __construct()
    {
        $this->url = 'https://justremote.co/api/jobs';
        $this->client = new Client();
    }

    function retrieveListings(): Collection
    {
        $response = $this->client->get($this->url);
        //TODO: Implement retrieveListings() method.
        return collect();
    }

    public function filter(Collection $listing): bool
    {
        return true;
    }
}
