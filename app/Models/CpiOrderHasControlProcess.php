<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CpiOrderHasControlProcess extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        'cpi_order_id',
        'form_control_process_id',
    ];

    public function cpi_orders()
    {
        return $this->belongsTo(CpiOrder::class, 'cpi_order_id', 'id');
    }
    public function cpi_order_has_control_process_photos()
    {
        return $this->hasMany(CpiOrderHasControlProcessPhoto::class, 'cpi_order_has_control_process_id', 'id');
    }

    public function form_control_process()
    {
        return $this->belongsTo(FormControlProcess::class, 'form_control_process_id', 'id');
    }
}
