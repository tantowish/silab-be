<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Inventory
 * 
 * @property int $id
 * @property string $item_name
 * @property string $no_item
 * @property string $condition
 * @property string $information
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|InventoryReserf[] $inventory_reserves
 * @property Collection|Room[] $rooms
 *
 * @package App\Models
 */
class Inventory extends Model
{
	protected $table = 'inventories';

	protected $fillable = [
		'item_name',
		'no_item',
		'condition',
		'information'
	];

	public function inventory_reserves()
	{
		return $this->hasMany(InventoryReserf::class);
	}

	public function rooms()
	{
		return $this->belongsToMany(Room::class, 'inventory_rooms')
					->withPivot('id')
					->withTimestamps();
	}
}
