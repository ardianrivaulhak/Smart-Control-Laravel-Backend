<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Line extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        "id",
        "name",
        "section_id"
    ];

    protected $hidden = ['laravel_through_key'];

    public function sections()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function cpi_orders()
    {
        return $this->hasManyThrough(CpiOrder::class, CpiOrderHasSection::class, 'line_id', 'id', 'id', 'cpi_order_id');
    }

    public function cpi_order_has_sections()
    {
        return $this->hasMany(CpiOrderHasSection::class, 'line_id', 'id');
    }
}
