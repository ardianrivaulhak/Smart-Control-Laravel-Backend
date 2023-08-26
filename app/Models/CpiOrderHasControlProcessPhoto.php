<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CpiOrderHasControlProcessPhoto extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        'cpi_order_has_control_process_id',
        'photo_url'
    ];

    public function cpi_order_has_control_process()
    {
        return $this->belongsTo(CpiOrderHasControlProcess::class, 'cpi_order_has_control_process_id', 'id');
    }
}
