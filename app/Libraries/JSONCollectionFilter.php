<?php

namespace App\Libraries;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class JSONCollectionFilter
{
    private string $json;

    public function __construct(string $json)
    {
        $this->json = $json;
    }

    function filter(Collection|array $collection): Collection
    {
        $result = collect();
        $conditions = json_decode($this->json);

        foreach ($collection as $listing) {
            if ($this->evaluateAnd($listing, $conditions)) {
                $result->push($listing);
            }
        }

        return $result;
    }

    private function evaluateCondition(Collection|array $listing, $condition): bool
    {
        $listing = collect($listing);
        switch($condition->operator){
            case 'in_array':
                return $this->evaluateInArray($condition, $listing);
                break;
            case 'like':
                return $this->evaluateLike($condition, $listing);
                break;
            case 'or' :
                return $this->evaluateOr($listing, $condition->value);
            case 'and' :
                return $this->evaluateAnd($listing, $condition->value);
            default:
                $listing = collect([$listing]);
                return $listing->contains($condition->field, $condition->operator, $condition->value);
        }
    }

    private function evaluateLike($condition, Collection $listing): bool
    {
        if ($condition->field === '*') {
            $fields = $listing->flatMap(fn ($val) => $val)->keys()->toArray();
            return collect($fields)->reduce(function($carry, $field) use ($listing, $condition) {
                return $carry || $this->recursiveContains($listing, $condition->value, $field);
            }, false);
        }

        if(Str::contains($condition->field, ',')) {
            $fields = explode(',', $condition->field);
            return collect($fields)->reduce(function($carry, $field) use ($listing, $condition) {
                return $carry || $this->recursiveContains($listing, $condition->value, $field);
            }, false);
        }

        return $this->recursiveContains($listing, $condition->value, $condition->field);
    }

    /**
     * @param $condition
     * @param Collection $listing
     * @return bool
     */
    protected function evaluateInArray($condition, Collection $listing): bool
    {
        //If apply on all the fields
        if($condition->field === '*') {
            $fields = $listing->flatMap(fn ($val) => $val)->keys()->toArray();
            return collect($fields)->reduce(function($carry, $field) use ($listing, $condition) {
                return $carry || in_array($condition->value, collect($listing)[$field]);
            }, false);
        }

        //If apply on multiple fields
        if(Str::contains($condition->field, ',')) {
            $fields = explode(',', $condition->field);
            return collect($fields)->reduce(function($carry, $field) use ($listing, $condition) {
                return $carry || in_array($condition->value, collect($listing)[$field]);
            }, false);
        }

        return in_array($condition->value, collect($listing)[$condition->field]);
    }

    private function evaluateOr(Collection|array $listing, array $conditions): bool
    {
        foreach ($conditions as $condition) {
            if ($this->evaluateCondition($listing, $condition)) {
                return true;
            }
        }
        return false;
    }

    private function evaluateAnd(Collection|array $listing, array $conditions): bool
    {
        foreach ($conditions as $condition) {
            if (!$this->evaluateCondition($listing, $condition)) {
                return false;
            }
        }
        return true;
    }

    private function recursiveContains($haystack, $needle, $key): bool
    {
        foreach ($haystack as $index=>$item) {
            if (is_array($item) || $item instanceof \Illuminate\Support\Collection) {
                if ($this->recursiveContains($item, $needle, $key)) {
                    return true;
                }
            }else if ($index === $key) {
                if (Str::contains($item, Str::lower($needle), true)) {
                    return true;
                }
            }
        }
        return false;
    }
}
