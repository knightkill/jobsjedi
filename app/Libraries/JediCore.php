<?php

namespace App\Libraries;

use App\Models\Board;
use App\Models\Monitor;
use App\Models\Listing;
use Illuminate\Support\Facades\Validator;

class JediCore
{

    public function __construct(
    ) {
    }

    public function processOpportunities(): void
    {
        $boards = Board::active()->get();
        $boards->each(function (Board $board) {
            $className = 'App\Libraries\Services\\' . ucfirst($board->slug);
            $board
                ->monitors()
                ->active()
                ->get()
                ->each(function ($monitor) use ($className) {
                    $instance = new $className($monitor);
                    if (!$instance instanceof BoardsContract) {
                        rd("Class $className doesn't implement BoardsContract interface and can't be used!");
                    }
                    $listings = $instance->retrieveListings();
                    $this->handleListings($listings, $instance, $monitor);
                });
        });
    }

    private function handleListings(
        \Illuminate\Support\Collection $listings,
        \App\Libraries\BoardsContract $instance,
        Monitor $monitor
    ): void
    {
        $listings = $instance->filter($listings);
        $listings->map(function($listing) use ($monitor) {

            $listingModel = Listing::firstOrCreate(
                [
                    'monitor_id' => $monitor->id,
                    'service_id' => $listing->get('id')
                ],
                $listing->get('fields')
            );

            collect($listing?->get('labels'))->map(function($label) use ($listingModel, $listing) {
                $labelArray = Validator::make($label, [
                    'name' => 'required|string|min:3|max:30',
                    'description' => 'string',
                ])->valid();
                if(!empty($labelArray) && !empty($labelArray['name'])){
                    return $listingModel->labels()
                        ->updateOrCreate(
                            [
                                'name' => $labelArray['name'],
                            ],
                            $labelArray
                        );
                }
            });

            collect($listing?->get('customFields'))?->mapWithKeys(function($customField,$key) use ($listingModel, $listing) {
                return $listingModel->customFields()
                    ->updateOrCreate(
                        [
                            'name' => $key,
                        ],
                        [
                            // If array or object, convert to json
                            'value' => is_array($customField) || is_object($customField) ? json_encode($customField) : $customField,
                        ]
                    );
            });

            return $listingModel->refresh();
        });
    }
}
