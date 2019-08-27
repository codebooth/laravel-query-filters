<?php

declare(strict_types=1);

namespace CodeBooth\QueryFilters;

use CodeBooth\QueryFilters\Exceptions\InvalidFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class QueryFilters
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $builder;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $filters;

    /**
     * @param \Illuminate\Support\Collection|null $filters
     */
    public function __construct(?Collection $filters)
    {
        $this->filters = $filters ?? new Collection;
    }

    /**
     * @param \Illuminate\Support\Collection $filters
     *
     * @return \CodeBooth\QueryFilters\QueryFilters
     */
    public function setFilters(Collection $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return \CodeBooth\QueryFilters\QueryFilters
     */
    public function setFilter(string $key, ?$value): self
    {
        $this->filters->put($key, $value);

        return $this;
    }

    /**
     * Apply the filters to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        $this->boot();

        $filters = $this->filters();

        foreach ($filters as $filter => $value) {
            $filter = Str::studly(Str::lower($filter));

            if ($filter === 'Order') {
                $orderBy = Str::studly(Str::lower($value));
                $method = "orderBy{$orderBy}";
            } else {
                $method = "filterBy{$filter}";
            }

            if (! method_exists($this, $method)) {
                continue;
            }

            if ($this->hasValue($value)) {
                $this->{$method}($value);
            } else {
                $this->{$method}();
            }
        }

        return $this->builder;
    }

    /**
     * @return array
     */
    public function filters(): array
    {
        if (! $this->filters instanceof Collection) {
            throw new InvalidFilters('Invalid filters format');
        }

        return $this->filters->all();
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    protected function hasValue($value): bool
    {
        if (is_array($value) && ! empty($value)) {
            return true;
        }

        if (is_string($value) && strlen($value)) {
            return true;
        }

        return ! is_null($value);
    }

    protected function boot()
    {
        // no default action
    }
}
