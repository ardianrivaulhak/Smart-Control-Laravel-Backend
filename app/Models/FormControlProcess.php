<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormControlProcess extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        'document_id',
        'section_id',
        'control_process_name'
    ];

    public function documents()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }

    public function sections()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function control_process_standards()
    {
        return $this->hasMany(ControlProcessStandard::class, 'form_control_process_id', 'id');
    }

    public function cpi_orders()
    {
        return $this->hasManyThrough(CpiOrder::class, ControlProcessStandard::class, 'form_control_process_id', 'id', 'id', 'cpi_order_id');
    }

    public function cpi_order_has_control_process_photos()
    {
        return $this->hasManyThrough(CpiOrderHasControlProcessPhoto::class, CpiOrderHasControlProcess::class, 'form_control_process_id', 'id', 'cpi_order_has_control_process_id', 'id');
    }

    public function cpi_order_has_control_process()
    {
        return $this->hasOne(CpiOrderHasControlProcess::class, 'form_control_process_id', 'id');
    }
}
