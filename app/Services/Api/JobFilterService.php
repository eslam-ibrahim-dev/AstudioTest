<?php

namespace App\Services\Api;

use App\Models\Job;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JobFilterService
{
    protected $query;
    protected $filters;

    public function __construct()
    {
        $this->query = Job::query();
    }

    public function filter($filters): Builder
    {
        if (is_string($filters)) {
            $this->filters = $this->parseFilterString($filters);
        } else {
            $this->filters = $filters;
        }
        $this->applyFilters($this->filters);

        return $this->query;
    }


    protected function parseFilterString(string $filterString): array
    {
        $parsed = [];

        if (Str::startsWith($filterString, '(') && Str::endsWith($filterString, ')')) {
            $filterString = substr($filterString, 1, -1);
        }
        $logicalParts = preg_split('/(AND|OR)(?![^(]*\))/', $filterString, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (count($logicalParts) > 1) {
            $currentOperator = null;
            $conditions = [];

            foreach ($logicalParts as $part) {
                $part = trim($part);

                if ($part === 'AND' || $part === 'OR') {
                    $currentOperator = strtolower($part);
                    continue;
                }
                $nestedCondition = $this->parseFilterString($part);

                if ($currentOperator) {
                    $conditions[] = [
                        'operator' => $currentOperator,
                        'condition' => $nestedCondition
                    ];
                } else {
                    $conditions[] = $nestedCondition;
                }
            }

            return $conditions;
        }

        // Process single condition
        if (preg_match('/^(.+?)\s*(>=|<=|=|!=|>|<|LIKE|HAS_ANY|IS_ANY|EXISTS|IN)\s*(.+)$/', $filterString, $matches)) {
            [, $field, $operator, $value] = $matches;

            // Check if this is an attribute filter
            if (Str::startsWith($field, 'attribute:')) {
                $attributeName = substr($field, 10);
                return [
                    'type' => 'attribute',
                    'name' => $attributeName,
                    'operator' => $operator,
                    'value' => $this->parseValue($value, $operator)
                ];
            }

            // Check if this is a relationship filter
            if (in_array($field, ['languages', 'locations', 'categories'])) {
                return [
                    'type' => 'relation',
                    'relation' => $field,
                    'operator' => $operator,
                    'value' => $this->parseValue($value, $operator)
                ];
            }

            // Regular field filter
            return [
                'type' => 'field',
                'field' => $field,
                'operator' => $operator,
                'value' => $this->parseValue($value, $operator)
            ];
        }

        return $parsed;
    }

    protected function parseValue(string $value, string $operator)
    {
        if (in_array($operator, ['HAS_ANY', 'IS_ANY', 'IN']) && preg_match('/^\((.*)\)$/', $value, $matches)) {
            return array_map('trim', explode(',', $matches[1]));
        }

        // Remove quotes if present
        if ((Str::startsWith($value, '"') && Str::endsWith($value, '"')) ||
            (Str::startsWith($value, "'") && Str::endsWith($value, "'"))
        ) {
            return substr($value, 1, -1);
        }

        // Try to cast boolean values
        if (in_array(strtolower($value), ['true', 'false'])) {
            return strtolower($value) === 'true';
        }
        return $value;
    }

    protected function applyFilters(array $filters)
    {
        if (isset($filters[0]) && is_array($filters[0])) {
            $this->query->where(function ($query) use ($filters) {
                foreach ($filters as $index => $condition) {
                    if (isset($condition['operator']) && isset($condition['condition'])) {
                        $method = $condition['operator'] === 'and' ? 'where' : 'orWhere';
                        $query->$method(function ($subQuery) use ($condition) {
                            $this->applyCondition($subQuery, $condition['condition']);
                        });
                    } else {
                        $method =  'where';
                        $query->$method(function ($subQuery) use ($condition) {
                            $this->applyCondition($subQuery, $condition);
                        });
                    }
                }
            });
            return;
        }
        $this->applyCondition($this->query, $filters);
    }
    protected function applyCondition(Builder $query, array $condition)
    {
        if (!isset($condition['type'])) {
            return;
        }

        switch ($condition['type']) {
            case 'field':
                $this->applyFieldFilter($query, $condition);
                break;

            case 'relation':
                $this->applyRelationFilter($query, $condition);
                break;

            case 'attribute':
                $this->applyAttributeFilter($query, $condition);
                break;
        }
    }
    protected function applyFieldFilter(Builder $query, array $condition)
    {
        $field = $condition['field'];
        $operator = $this->mapOperator($condition['operator']);
        $value = $condition['value'];
        // Handle special operators
        if ($condition['operator'] === 'LIKE') {
            $query->where($field, 'LIKE', "%{$value}%");
            return;
        }

        if ($condition['operator'] === 'IN') {
            $query->whereIn($field, (array) $value);
            return;
        }

        $query->where($field, $operator, $value);
    }

    protected function applyRelationFilter(Builder $query, array $condition)
    {
        $relation = $condition['relation'];
        $operator = $condition['operator'];
        $value = $condition['value'];
        switch ($operator) {
            case '=':
                foreach ((array) $value as $val) {
                    $query->whereHas($relation, function ($q) use ($relation, $val) {
                        if ($relation === 'languages' || $relation === 'categories') {
                            $q->where('name', $val);
                        } else {
                            $q->where('city', $val);
                        }
                    });
                }
                break;

            case 'HAS_ANY':
                $query->whereHas($relation, function ($q) use ($relation, $value) {
                    if ($relation === 'languages' || $relation === 'categories') {
                        $q->whereIn('name', (array) $value);
                    } else {
                        $q->whereIn('city', (array) $value);
                    }
                });
                break;

            case 'IS_ANY':
                $query->whereHas($relation, function ($q) use ($relation, $value) {
                    if ($relation === 'languages' || $relation === 'categories') {
                        $q->whereIn('name', (array) $value);
                    } else {
                        $q->whereIn('city', (array) $value);
                    }
                });
                break;

            case 'EXISTS':
                $query->has($relation);
                break;
        }
    }
    protected function applyAttributeFilter(Builder $query, array $condition)
    {
        $attributeName = $condition['name'];
        $operator = $this->mapOperator($condition['operator']);
        $value = $condition['value'];

        $attribute = Attribute::where('name', $attributeName)->first();

        if (!$attribute) {
            return;
        }

        switch ($attribute->type) {
            case 'boolean':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                $value = $value ? '1' : '0';
                break;

            case 'number':
                if (!is_numeric($value)) {
                    return;
                }
                break;

            case 'date':
                if (!strtotime($value)) {
                    return;
                }
                break;
        }

        if ($condition['operator'] === 'LIKE') {
            $query->whereHas('attributes', function ($q) use ($attribute, $value) {
                $q->where('attribute_id', $attribute->id)
                    ->where('value', 'LIKE', "%{$value}%");
            });
            return;
        }

        if ($condition['operator'] === 'IN') {
            $query->whereHas('attributes', function ($q) use ($attribute, $value) {
                $q->where('attribute_id', $attribute->id)
                    ->whereIn('value', (array) $value);
            });
            return;
        }
        $query->whereHas('attributes', function ($q) use ($attribute, $operator, $value) {
            $q->where('attribute_id', $attribute->id)
                ->where('value', $operator, $value);
        });
    }
    protected function mapOperator(string $operator): string
    {
        $map = [
            '=' => '=',
            '!=' => '!=',
            '>' => '>',
            '<' => '<',
            '>=' => '>=',
            '<=' => '<=',
            'LIKE' => 'LIKE',
        ];

        return $map[$operator] ?? '=';
    }
}
