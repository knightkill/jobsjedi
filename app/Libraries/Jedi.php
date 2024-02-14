<?php

namespace App\Libraries;

use App\Models\Board;
use App\Models\Listing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class Jedi
{

    public function __construct(
    ) {
    }

    public function processOpportunities(): void
    {
        $boards = Board::active()->get();
        $boards->each(function ($board) {
            $className = 'App\Libraries\Services\\' . ucfirst($board->slug);
            $instance = new $className($board);
            if (!$instance instanceof BoardsContract) {
                rd("Class $className doesn't implement BoardsContract interface and can't be used!");
            }
            $listings = $instance->retrieveListings();
            $this->handleListings($listings, $instance, $board);
        });
    }

    private function handleListings(
        \Illuminate\Support\Collection $listings,
        \App\Libraries\BoardsContract $instance,
        Board $board
    ): void
    {
        //$listings = $board->listings()->makeMany($listings);
        $listings = $listings->filter(function ($listing) use ($instance) {
            return $instance->filter($listing);
        });
        $listings->map(function($listing) use ($board) {

            $listingModel = Listing::firstOrCreate(
                [
                    'board_id' => $board->id,
                    'service_id' => $listing->get('id')
                ],
                $listing->get('fields')
            );

            collect($listing?->get('labels'))->map(function($label) use ($listingModel, $listing) {
                $labelArray = Validator::make($label, [
                    'name' => 'required|string|unique:labels,name|min:3|max:30',
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
                            'value' => $customField,
                        ]
                    );
            });
            return $listingModel->refresh();
        });
    }
}
