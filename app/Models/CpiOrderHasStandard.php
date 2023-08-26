<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CpiOrderHasStandard extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        'cpi_order_id',
        'control_process_standard_id',
        'status',
        'description'
    ];

    public function cpi_orders()
    {
        return $this->belongsTo(CpiOrder::class, 'cpi_order_id', 'id');
    }

    public function control_process_standards()
    {
        return $this->belongsTo(ControlProcessStandard::class, 'control_process_standard_id', 'id');
    }
}
