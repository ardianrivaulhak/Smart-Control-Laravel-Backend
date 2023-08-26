<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IsCpiOrderCorrected extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        'cpi_order_before_id',
        'cpi_order_after_id',
    ];

    public function cpi_order_before()
    {
        return $this->belongsTo(CpiOrder::class, 'cpi_order_before_id', 'id');
    }
    public function cpi_order_after()
    {
        return $this->belongsTo(CpiOrder::class, 'cpi_order_after_id', 'id');
    }
}
