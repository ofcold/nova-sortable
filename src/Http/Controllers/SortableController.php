<?php

namespace Ofcold\NovaSortable\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class SortableController extends Controller
{
	public function store(NovaRequest $request)
	{
		$model = get_class($request->newResource()->model());

		$items = json_decode(base64_decode($request->items));

		foreach ($items as $item) {
			tap($model::find($item->id), function($entry) use ($model, $item) {
				$entry->{$model::orderColumnName()} = $item->sort_order;
			})->save();
		}

		return response()->json();
	}

}
