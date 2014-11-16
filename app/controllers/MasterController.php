<?php

class MasterController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Controller
	|--------------------------------------------------------------------------
	|
	| TODO: Write comments here.
	|
	*/

	public function home()
	{

		// Check whether any filters are active.
		$filters = Input::get('filter');

		if (count($filters))
		{
			// Loop through all active filters and construct the aggregate query.
			$whereraw = array();
			foreach ($filters as $filter)
			{
				$whereraw[] = 'categoryName = "' . $filter . '"';
			}

			// Retrieve the list of selected items.
			$items = Item::whereRaw(implode(' or ', $whereraw))->get();
		}
		else
		{
			$items = Item::all();
		}

		// Loop through them all to combine the same items.
		$table = array();
		$simple_array = array(); // keep track of which items have already been counted

		foreach ($items as $item)
		{
			if (in_array($item->typeID, $simple_array))
			{
				// This item is already in the table.
				$table[$item->typeID]->qty += $item->qty;
			}
			else
			{
				$table[$item->typeID] = (object) array(
					"qty"		=> $item->qty,
					"typeName"	=> $item->typeName,
					"category"	=> $item->categoryName,
					"meta"		=> $item->metaGroupName,
				);
				$simple_array[] = $item->typeID;
			}
		}

		// Sort the list of items by quantity.
		usort($table, function ($a, $b)
		{
			if ($a->qty == $b->qty) {
				return 0;
			}
			return ($a->qty > $b->qty) ? -1 : 1;
		});

		// Load the template to display all the items.
		return View::make('home')->with('items', $table)->with('filters', $filters);

	}

}