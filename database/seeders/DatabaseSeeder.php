<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Board;
use App\Models\CustomField;
use App\Models\Label;
use App\Models\Listing;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Label::factory(5)->create();

        Board::factory(5)->create();

        Board::all()->each(function ($board) {
            $board->listings()->saveMany(Listing::factory(rand(0,15))->make());
        });

        //Listing::factory(30)->create();

        Listing::all()->each(function (Listing $listing) {
            $listing->labels()->attach(
                Label::inRandomOrder()->limit(rand(1, 5))->get()
            );

            $listing->customFields()->saveMany(
                CustomField::factory(rand(1, 5))->make()
            );
        });
    }
}
