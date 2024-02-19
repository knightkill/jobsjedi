<?php

namespace App\Libraries;


use Illuminate\Support\Collection;

interface BoardsContract
{

    function retrieveListings(): Collection;

    public function filter(Collection $listings): Collection;
}
