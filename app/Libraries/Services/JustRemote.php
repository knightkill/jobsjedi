<?php

namespace App\Libraries\Services;

use App\Models\Monitor;
use App\Traits\GenericBoardTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class JustRemote
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

        $client = new \Goutte\Client();
        $url = $this->makeURL();
        $crawler = $client->request('GET', $url);
        $a_sections = $crawler
            ->filter("div[class*='job-listings__Right']>div[class*='new-job-item__JobItemWrapper']");
        //$crawler->filterXPath("//div[contains(@class,'job-listings__Right')]/div[contains(@class,'new-job-item__JobItemWrapper')]")->count()
        foreach($a_sections as $index => $a_section) {
            $d_section = $a_section->firstElementChild;
            $image = $d_section->firstElementChild;
            $b_sections = $image->nextElementSibling->firstElementChild->firstElementChild;
            $company_name = $b_sections->nextElementSibling;
            $open_position = $company_name->nextElementSibling;
            $working_hours_cum_salary_range = Str::squish($open_position->nextElementSibling->nodeValue);
            $working_hours = Str::squish(Str::before($working_hours_cum_salary_range,'-'));
            $salary_range = Str::squish(Str::after($working_hours_cum_salary_range,'-'));

            $additional_section = $image->nextElementSibling->firstElementChild->nextElementSibling->firstElementChild;
            $period_passed = Str::squish($additional_section->firstElementChild->nextElementSibling->nodeValue);
            $posted_at = $this->fromTimeAgo($period_passed);
            $location = Str::squish($additional_section->firstElementChild->nextElementSibling->previousElementSibling->nodeValue) ?? null;
            $tagsSection = $additional_section->nextElementSibling;
            if(!empty($tagsSection)){
                $tag = $tagsSection?->firstElementChild;
                $tags[]['name'] = Str::squish($tag?->nodeValue);
                while($tag = $tag?->nextElementSibling){
                    $tags[]['name'] = Str::squish($tag?->nodeValue);
                }
            }

            $href = $a_section->getAttribute('href');
            $serviceId = Str::afterLast($href, '/');

            $listing = collect(
                [
                    'id' => $serviceId,
                    'fields' => [
                        'title' => $open_position?->nodeValue ?? null,
                        'company' => $company_name?->nodeValue ?? null,
                        'working_hours' => $working_hours,
                        'pay_range' => $salary_range,
                        'location' => $location,
                        'link' => "https://larajobs.com" . $href,
                        'posted_at' => $posted_at,
                    ],
                    'labels' => $tags ?? null,
                    'customFields' => []
                ]
            );
            $result = $result->push($listing);
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
}
