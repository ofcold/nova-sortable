# Nova sortable

> Adds sorting functionality to the Laravel Nova's index resource


## Installing

```bash
composer require ofcold/nova-sortable
```


## Useing

- Add a sort field to your database migrations file.

***Example***

```php
$table->unsignedInteger('sort_order')->nullable();
```

- Trait entry

```php

use Ofcold\NovaSortable\SortableTrait;

class Entry extends Model
{
	use SortableTrait;
}
```

- Specify whether the resource needs to be sorted.

```php
class Example extends Resource
{
	/**
	 * Build an "index" query for the given resource.
	 *
	 * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public static function indexQuery(NovaRequest $request, $query)
	{
		$query->when(empty($request->get('orderBy')), function (Builder $q) {
			$q->getQuery()->orders = [];
			return $q->orderBy(static::$model::orderColumnName());
		});

		return $query;
	}

	/**
	 * Prepare the resource for JSON serialization.
	 *
	 * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
	 * @param  \Illuminate\Support\Collection  $fields
	 *
	 * @return array
	 */
	public function serializeForIndex(NovaRequest $request, $fields = null)
	{
		return array_merge(parent::serializeForIndex($request, $fields), [
			'sortable'	=> true
		]);
	}
}
```


## Change sort field name.

> You only need to change the method 'orderColumnName' in your entry.

```php
/*
 * Determine the column name of the order column.
 */
public static function orderColumnName(): string
{
	return 'your sort order column name';
}
```


## Demos

<img src="https://github.com/ofcold/nova-sortable/raw/master/demos.gif?sanitize=true">
