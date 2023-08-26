<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CpiOrderExit extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = ['cpi_order_id', 'reason'];

    public function cpi_orders()
    {
        return $this->belongsTo(CpiOrder::class, 'cpi_order_id', 'id');
    }
}
