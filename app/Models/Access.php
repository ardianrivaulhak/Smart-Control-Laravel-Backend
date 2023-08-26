<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Uuids;

class Access extends Model
{
    use SoftDeletes;
    use Uuids;
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
    ];

    public function accessPermissions()
    {
        return $this->hasMany(AccessPermission::class, 'access_id', 'id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'access_permissions', 'access_id', 'role_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'access_permissions', 'access_id', 'permission_id')
            ->withPivot('status', 'is_disable');
    }
}
