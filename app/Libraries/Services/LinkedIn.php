<?php

namespace App\Libraries\Services;

use App\Libraries\BoardsContract;
use App\Models\Board;
use Goutte\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

class LinkedIn implements BoardsContract
{
    private string $url;

    public function __construct(
        public Board $board
    )
    {
        $this->url = 'https://www.linkedin.com/jobs/search/?currentJobId=3462810155&distance=25&geoId=92000000&keywords=remote%20laravel&position=7&pageNum=';

    }

    function retrieveListings(): Collection
    {
        $result = collect();

        for ($i = 1; $i < 20; $i++) {
            $client = new Client();
            $crawler = $client->request('GET', $this->url.$i);
            $a_sections = $crawler
                ->filter('ul.jobs-search__results-list');
            $a_sections = $a_sections
                ->children('li')
                ->each(function(Crawler $node) use ($result) {
                    $title = $node->filter('h3.base-search-card__title');
                    $title = $title->count()>0 ? $title->text() : null;
                    $company = $node->filter('h4.base-search-card__subtitle');
                    $company = $company->count()>0 ? $company->text() : null;
                    $location = $node->filter('span.job-search-card__location');
                    $location = $location->count()>0 ? $location->text() : null;
                    $posted_at = $node->filter('time.job-search-card__listdate');
                    $posted_at = $posted_at->count()>0 ? Carbon::parse($posted_at->attr('datetime')) : null;
                    $href = $node->filter('a.base-card__full-link');
                    $href = $href->count()>0 ? $href->attr('href') : null;
                    $salary = $node?->filter('span.job-search-card__salary-info');
                    $salary = $salary->count() > 0 ? $salary->text() : null;
                    $serviceId = $node->filter('div.base-card');
                    $serviceId = $serviceId->count()>0 ? $serviceId->attr('data-entity-urn') : null;

                    $result->push(collect(
                        [
                            'id' => $serviceId,
                            'fields' => [
                                'title' => $title ?? null,
                                'company' => $company ?? null,
                                'location' => $location ?? null,
                                'posted_at' => $posted_at ?? null,
                                'pay_range' => $salary ?? null,
                                'link' => $href ?? null,
                            ],
                        ]
                    ));
                });
        }

        return $result;
    }

    public function filter(Collection $listing): bool
    {
        return true;
    }
}
