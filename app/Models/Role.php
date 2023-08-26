<?php

namespace App\Models;

use App\Traits\Uuids;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;
    use Uuids;
    use HasFactory;

    protected $fillable = [
        'name'
    ];


    public function accessPermissions()
    {
        return $this->hasMany(AccessPermission::class, 'role_id', 'id');
    }


    public function accesses()
    {
        return $this->belongsToMany(Access::class, 'access_permissions', 'role_id', 'access_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'access_permissions', 'role_id', 'permission_id');
    }
}
