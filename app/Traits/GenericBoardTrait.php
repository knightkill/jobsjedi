<?php

namespace App\Traits;

use App\Libraries\JSONCollectionFilter;
use Illuminate\Support\Collection;

trait GenericBoardTrait
{

    public function filter(Collection $listings): Collection
    {
        return (new JSONCollectionFilter(json_encode($this->monitor->filter,JSON_OBJECT_AS_ARRAY)))->filter($listings);
    }

    public function settings() {
        return $this->monitor->monitorSettings()->get();
    }
}
