<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Job;
use App\Models\Attribute;

class JobFilterService
{
    public function applyFilters(Builder $query, $filter)
    {
        $filterParts = $this->parseFilter($filter);
        foreach ($filterParts as $part) {
            $this->applyFilterCondition($query, $part);
        }
    }

    public function parseFilter($filter)
    {
        return preg_split('/(AND|OR)/', $filter, -1, PREG_SPLIT_DELIM_CAPTURE);
    }

    public function applyFilterCondition(Builder $query, $condition)
    {
        if (strpos($condition, 'job_type') !== false) {
            $this->applyJobTypeFilter($query, $condition);
        } elseif (strpos($condition, 'languages') !== false) {
            $this->applyLanguagesFilter($query, $condition);
        } elseif (strpos($condition, 'locations') !== false) {
            $this->applyLocationsFilter($query, $condition);
        } elseif (strpos($condition, 'attribute:') !== false) {
            $this->applyAttributeFilter($query, $condition);
        }
    }

    public function applyJobTypeFilter(Builder $query, $condition)
    {
        preg_match('/job_type=([a-zA-Z-]+)/', $condition, $matches);
        if (isset($matches[1])) {
            $query->where('job_type', $matches[1]);
        }
    }

    public function applyLanguagesFilter(Builder $query, $condition)
    {
        preg_match('/languages\s*HAS_ANY\s*\((.*?)\)/', $condition, $matches);

        if (isset($matches[1])) {
            $languages = array_map('trim', explode(',', $matches[1]));
            $query->with(['languages' => function ($query) use ($languages) {
                $query->whereIn('name', $languages);
            }]);
        }
    }

    public function applyLocationsFilter(Builder $query, $condition)
    {
        preg_match('/locations\s*IS_ANY\s*\((.*?)\)/', $condition, $matches);
        if (isset($matches[1])) {
            $locations = explode(',', $matches[1]);
            $query->with(['locations' => function ($query) use ($locations) {
                $query->whereIn('city', $locations);
            }]);
        }
    }

    public function applyAttributeFilter(Builder $query, $condition)
    {
        preg_match('/attribute:(\w+)\s*(>=|<=|=)\s*(\d+)/', $condition, $matches);
        if (isset($matches[1], $matches[2], $matches[3])) {
            $attribute = $matches[1];
            $operator = $matches[2];
            $value = $matches[3];

            $query->whereHas('attributes', function ($query) use ($attribute, $operator, $value) {
                $query->where('attribute_id', function ($query) use ($attribute) {
                    $query->select('id')->from('attributes')->where('name', $attribute)->limit(1);
                })->where('value', $operator, $value);
            });

            $query->with(['attributes' => function ($query) use ($attribute, $operator, $value) {
                $query->where('attribute_id', function ($query) use ($attribute) {
                    $query->select('id')->from('attributes')->where('name', $attribute)->limit(1);
                })->where('value', $operator, $value);
            }]);
        }
    }
}
