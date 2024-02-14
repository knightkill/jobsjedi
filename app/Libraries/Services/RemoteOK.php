<?php

namespace App\Libraries\Services;

use App\Libraries\BoardsContract;
use Goutte\Client;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class RemoteOK implements BoardsContract
{

    private string $url;
    private Client $client;

    public function __construct(
    )
    {
        $this->url = 'https://remoteok.com/remote-laravel-jobs';
        $this->url = 'https://remoteok.com/?tags=laravel&action=get_jobs&offset=';
        $this->client = new Client(HttpClient::create([
            'headers' => [        'authority' => 'remoteok.com',        'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',        'accept-language' => 'en-GB,en-US;q=0.9,en;q=0.8',        'cache-control' => 'max-age=0',        'cookie' => 'ref=https%3A%2F%2Fremoteok.com%2F; new_user=false; visits=10; visit_count=5; adShuffler=1; hidden_subscribe_to_newsletter=true',        'sec-ch-ua' => '"Not_A Brand";v="99", "Google Chrome";v="109", "Chromium";v="109"',        'sec-ch-ua-mobile' => '?0',        'sec-ch-ua-platform' => '"macOS"',        'sec-fetch-dest' => 'document',        'sec-fetch-mode' => 'navigate',        'sec-fetch-site' => 'same-origin',        'sec-fetch-user' => '?1',        'upgrade-insecure-requests' => 1,        'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36']
        ]));
    }

    function retrieveListings(): Collection
    {
        $result = collect();
        $i = 0;
        do {
            $html = $this->fetchData($i);
            $crawler = new Crawler($html);
            $crawler->filter('tr.job')->each(function ($node) use (&$result) {
                $id = $node->attr('data-id');
                $closed = $node->filter('span.closed')->count();
                $title = $node->filter('h2[itemprop="title"]')->text();
                $company = $node->filter('h3[itemprop="name"]')->text();
                $url = $node->filter('a[itemprop="url"]')->attr('href');
                $location = $node->filter('td.company_and_position .location')->first()->text();
                $salary = $node->filter('td.company_and_position .location')->first()->nextAll()->text();
                $tags = $node->filter('td a div.tag')->each(function ($node) {
                    return ['name' => $node->text()];
                });
                $posted_at = $node->filter('td.time time')->attr('datetime');
                if($closed) {
                    $result->push(collect([
                        'id' => $id,
                        'fields' => [
                            'title' => $title,
                            'company' => $company,
                            'link' => $url,
                            'location' => $location,
                            'pay_range' => $salary,
                            'posted_at' => $posted_at,
                        ],
                        'labels' => $tags ?? null,
                        'customFields' => null,
                    ]));
                }
            });
            $i += 19;
        } while($crawler->filter('tr.job')->count() > 0);

        return $result;
    }

    public function filter(Collection $listing): bool
    {
        return true;
    }

    private function fetchData(int $offset) : string
    {
        return HttpClient::create([
            'headers' => [        'authority' => 'remoteok.com',        'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',        'accept-language' => 'en-GB,en-US;q=0.9,en;q=0.8',        'cache-control' => 'max-age=0',        'cookie' => 'ref=https%3A%2F%2Fremoteok.com%2F; new_user=false; visits=10; visit_count=5; adShuffler=1; hidden_subscribe_to_newsletter=true',        'sec-ch-ua' => '"Not_A Brand";v="99", "Google Chrome";v="109", "Chromium";v="109"',        'sec-ch-ua-mobile' => '?0',        'sec-ch-ua-platform' => '"macOS"',        'sec-fetch-dest' => 'document',        'sec-fetch-mode' => 'navigate',        'sec-fetch-site' => 'same-origin',        'sec-fetch-user' => '?1',        'upgrade-insecure-requests' => 1,        'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36']
        ])->request('GET', $this->url.$offset)->getContent();
    }
}
