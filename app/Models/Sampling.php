<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sampling extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        'type_name',
        'name',
        'std',
        'rh',
        'lh',
        'judgement'
    ];

    public function samplings()
    {
        return $this->hasManyThrough(CpiOrder::class, CpiOrderHasSampling::class, 'sampling_id', 'id', 'id', 'cpi_order_id');
    }
}
