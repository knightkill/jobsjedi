<?php

namespace App\Libraries\Services;

use App\Libraries\BoardsContract;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WorkingNomads implements BoardsContract
{

    private string $url;
    private Client $client;

    public function __construct()
    {
        $this->url = 'https://www.workingnomads.com/jobsapi/job/_search?sort=expired:asc,premium:desc,pub_date:desc&_source=company,category_name,description,locations,location_base,salary_range,salary_range_short,number_of_applicants,instructions,id,external_id,slug,title,pub_date,tags,source,apply_url,premium,expired,use_ats,position_type&size=500&from=0&q=laravel*';
        $this->client = new Client();

    }

    function retrieveListings(): Collection
    {
        $response = $this->client->get($this->url);
        $responseData = $response->getBody()->getContents();
        $responseData = json_decode($responseData,1)['hits']['hits'];

        $result = collect();

        foreach($responseData as $job) {
            $job = $job['_source'];
            $result->push(collect(
                [
                    'id' => $job['id'],
                    'fields' => [
                        'title' => $job['title'] ?? null,
                        'company' => $job['company'] ?? null,
                        'location' => $job['location_base'] ?? null,
                        'posted_at' => Carbon::parse($job['pub_date']) ?? null,
                        'pay_range' => $job['salary_range'] ?? null,
                        'link' => $job['apply_url'] ?? null,
                        'description' => $job['description'] ?? null,
                    ],
                    'labels' => collect($job['tags'])->map(function($tag) {
                            return [
                                'name' => $tag,
                            ];
                        })->toArray(),
                    'customFields' => [
                        'source' => $job['source'] ?? null,
                        'position_type' => $job['position_type'] ?? null,
                        'applicants' => $job['number_of_applicants'] ?? null,
                        'instructions' => $job['instructions'] ?? null,
                        'category_name' => $job['category_name'] ?? null,
                    ]
                ]
            ));
        }

        return $result;
    }

    public function filter(Collection $listing): bool
    {
        return true;
        // TODO: Implement filter() method.
    }
}
