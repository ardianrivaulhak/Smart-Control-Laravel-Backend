<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'name',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function lines()
    {
        return $this->hasMany(Line::class, 'section_id', 'id');
    }

    public function line()
    {
        return $this->hasOneThrough(Line::class, CpiOrderHasSection::class, 'section_id', 'id', 'id', 'line_id');
    }

    public function form_control_process()
    {
        return $this->hasMany(FormControlProcess::class, 'section_id', 'id');
    }

    public function cpi_orders()
    {
        return $this->hasManyThrough(CpiOrder::class, CpiOrderHasSection::class, 'section_id', 'id', 'id', 'cpi_order_id');
    }

    public function cpi_order_has_sections()
    {
        return $this->hasMany(CpiOrderHasSection::class, 'section_id', 'id');
    }
}
