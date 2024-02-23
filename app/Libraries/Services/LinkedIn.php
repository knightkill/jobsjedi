<?php

namespace App\Libraries\Services;

use App\Libraries\BoardsContract;
use App\Models\Monitor;
use App\Traits\GenericBoardTrait;
use Goutte\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Symfony\Component\DomCrawler\Crawler;

class LinkedIn implements BoardsContract
{

    use GenericBoardTrait;

    private string $url = 'https://www.linkedin.com/jobs/search';

    public function __construct(
        public Monitor $monitor
    )
    {}

    function retrieveListings(): Collection
    {
        $result = collect();

        for ($i = 1; $i < 20; $i++) {
            $url = $this->makeURL($i);
            $client = new Client();
            $crawler = $client->request('GET', url($this->url,http_build_query(['pageNum' => $i ?? 1])));
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

    private function makeURL($pageNum): string
    {
        $query_array = [];
        //Add location as query parameter if exists
        if($this->settings()->where('key','location')->isNotEmpty()) {
            $query_array['location'] = $this->settings()->where('key','location')->first()->value;
        }
        if($this->settings()->where('key','keywords')->isNotEmpty()) {
            $query_array['keywords'] = $this->settings()->where('key','keywords')->first()->value;
        }

        if($pageNum) {
            $query_array['pageNum'] = $pageNum;
        }

        return URL::to($this->url . '?' . http_build_query($query_array),true);
    }
}
