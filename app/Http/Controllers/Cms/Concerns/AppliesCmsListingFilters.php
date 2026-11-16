<?php

namespace App\Http\Controllers\Cms\Concerns;

use App\Cms\CmsWorkflowStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait AppliesCmsListingFilters
{
    /**
     * @param  list<string>  $searchColumns
     */
    protected function applyListingFilters(Builder $query, Request $request, array $searchColumns): Builder
    {
        if ($request->filled('q')) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->string('q')->toString()).'%';
            $query->where(function (Builder $inner) use ($searchColumns, $term) {
                foreach ($searchColumns as $index => $column) {
                    if ($index === 0) {
                        $inner->where($column, 'like', $term);
                    } else {
                        $inner->orWhere($column, 'like', $term);
                    }
                }
            });
        }

        if ($request->filled('status') && in_array($request->string('status')->toString(), CmsWorkflowStatus::all(), true)) {
            $query->where('status', $request->string('status')->toString());
        }

        return $query->latest();
    }
}
