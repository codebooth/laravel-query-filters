<?php

namespace CodeBooth\QueryFilters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;

class QueryFiltersServiceProvider extends ServiceProvider
{
    public function register()
    {
        Builder::macro('filter', function (QueryFilters $filters) {
            return $filters->apply($this);
        });
    }
}
