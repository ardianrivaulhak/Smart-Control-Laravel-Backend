<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use SoftDeletes;
    use Uuids;
    use HasFactory;

    protected $keyType = 'string';
    protected $fillable = [
        'name'
    ];

    public function accessPermissions()
    {
        return $this->hasMany(AccessPermission::class, 'permission_id', 'id');
    }
    public function accesses()
    {
        return $this->belongsToMany(Access::class, 'access_permissions', 'permission_id', 'access_id')
            ->withPivot('status', 'is_disable');
    }
}
