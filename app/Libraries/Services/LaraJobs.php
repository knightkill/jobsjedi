<?php

namespace App\Libraries\Services;

use App\Libraries\BoardsContract;
use App\Models\Board;
use App\Models\Listing;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LaraJobs implements BoardsContract
{
    public function __construct(
        public Board $board
    )
    {
    }

    function retrieveListings(): Collection
    {
        $result = collect();

        $client = new Client();
        $crawler = $client->request('GET', 'https://larajobs.com');
        $a_sections = $crawler
            ->filter('a.job-link.group');
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

    public function filter(Collection $listing): bool
    {
        return true;
    }

    public function fromTimeAgo($period_passed): ?string {
        try {
            $from = ['mo','mos','s', 'm', 'h', 'd', 'w','y'];
            $to = [' month',' months', ' second', ' minute', ' hour', ' day', ' week',' year'];
            foreach($from as $index => $from_item) {
                if(Str::contains($period_passed, $from_item)) {
                    $period_passed = Str::replaceFirst($from_item, $to[$index], $period_passed);
                    break;
                }
            }
            $number = (int) Str::before($period_passed, ' ');
            if($number > 1 && !Str::endsWith($period_passed, 's')) {
                $period_passed = Str::plural($period_passed);
            }
            return Carbon::parse(strtotime($period_passed . ' ago'));
        } catch (\Exception $e) {
            return null;
        }
    }

    function str_replace_first($search, $replace, $subject) {
        $pos = strpos($subject, $search);
        if ($pos !== false) {
            return substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }
}
