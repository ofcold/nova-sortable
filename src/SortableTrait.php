<?php

namespace Ofcold\NovaSortable;


use Illuminate\Database\Eloquent\Builder;


trait SortableTrait
{
	/*
	 * Determine the column name of the order column.
	 */
	public static function orderColumnName(): string
	{
		return 'sort_order';
	}

	public function scopeOrdered(Builder $query, string $direction = 'asc')
	{
		return $query->orderBy(static::orderColumnName(), $direction);
	}
}
