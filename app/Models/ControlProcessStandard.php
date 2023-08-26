<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ControlProcessStandard extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        'form_control_process_id',
        'name'
    ];

    public function form_control_process()
    {
        return $this->belongsTo(FormControlProcess::class, 'form_control_process_id', 'id');
    }

    public function cpi_order_has_standards()
    {
        return $this->hasMany(CpiOrderHasStandard::class, 'control_process_standard_id', 'id');
    }
}
