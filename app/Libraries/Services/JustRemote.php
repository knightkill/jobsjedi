<?php

namespace App\Libraries\Services;

use App\Libraries\BoardsContract;
use App\Models\Monitor;
use App\Traits\GenericBoardTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpClient\HttpClient;

class JustRemote implements BoardsContract
{

    use GenericBoardTrait;


    public function __construct(
        protected readonly Monitor $monitor
    )
    {
    }

    public function retrieveListings(): Collection
    {
        $result = collect();

        $this->client = new \Goutte\Client(HttpClient::create([
            'headers' => [
                'authority' => 'justremote.co',
                'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'accept-language' => 'en-GB,en-US;q=0.9,en;q=0.8',
                'cache-control' => 'max-age=0',
                'cookie' => 'ref=https%3A%2F%2Fremoteok.com%2F; new_user=false; visits=10; visit_count=5; adShuffler=1; hidden_subscribe_to_newsletter=true',
                'sec-ch-ua' => '"Not_A Brand";v="99", "Google Chrome";v="109", "Chromium";v="109"',
                'sec-ch-ua-mobile' => '?0',
                'sec-ch-ua-platform' => '"macOS"',
                'sec-fetch-dest' => 'document',
                'sec-fetch-mode' => 'navigate',
                'sec-fetch-site' => 'same-origin',
                'sec-fetch-user' => '?1',
                'upgrade-insecure-requests' => 1,
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'
            ]
        ]));
        $scriptContent = $this->client->request('GET',$this->makeURL())->filterXPath('//script[contains(text(), "__PRELOADED_STATE__")]')->first()->text();

        preg_match('/window\.__PRELOADED_STATE__ = ({.*})/s', $scriptContent, $matches);
        if (isset($matches[1])) {
            $allJobs = json_decode($matches[1], true)['jobsState']['entity']['all'];
            $allJobs = collect($allJobs)->map(fn($job) => $this->processEachJob($job))->toArray();
            $result = $result->merge(collect($allJobs)->map(fn($job) => $this->travelInside($job)));
        }

        return $result;
    }

    private function makeURL(): string
    {
        $base_url = "https://justremote.co/remote-jobs";
        /*if($this->settings()->where('key','filter_tech')->isNotEmpty()) {
            return "$base_url/{$this->settings()->where('key','filter_tech')->first()->value}-jobs";
        }*/
        return $base_url;
    }

    private function travelInside(array $job): Collection
    {
        $job = collect($job);
        return collect([
            'id' => $job->pull('id'),
            'fields' => [
                'title' => $job->pull('title'),
                'company' => $job->pull('company_name'),
                'link' => 'https://justremote.co/'.$job->pull('href'),
                'location' => implode(',' , $job->pull('location_restrictions',[])),
                // TODO: Obtain pay_range using ML 'pay_range' => $salary,
                'posted_at' => Carbon::parse($job->pull('raw_date')),
                'description' => $job->pull('about_role'),
            ],
            'labels' => collect($job->pull('technology_list',[]))->map(fn($tag) => ['name' => $tag['label']])->toArray(),
            'customFields' => $job->toArray(),
        ]);
    }

    private function processEachJob(array $job) : array
    {
        $href = "https://justremote.co/{$job['href']}";
        return $this->client->request('GET',$href)->filterXPath('//script[contains(text(), "__PRELOADED_STATE__")]')->each(function($node) use ($job) {
            $scriptContent = $node->text();
            // Parse the script content to extract the __PRELOADED_STATE__ value
            preg_match('/window\.__PRELOADED_STATE__ = ({.*})/s', $scriptContent, $matches);
            if (isset($matches[1])) {
                $jobDetails = json_decode($matches[1], true)['singleJobState'][$job['category']];
                return array_merge($job, $jobDetails);
            }else {
                return $job;
            }
        })[0] ?? $job;
    }
}
