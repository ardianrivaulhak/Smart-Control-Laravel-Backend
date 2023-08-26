<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Problem extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        'part_name',
        'type_name',
        'name',
        'lot',
        'reason',
        'action',
        'reject',
        'ng',
        'ok',
        'identity'
    ];

    public function cpi_orders()
    {
        return $this->hasManyThrough(CpiOrder::class, CpiOrderHasProblem::class, 'problem_id', 'id', 'id', 'cpi_order_id');
    }
}
