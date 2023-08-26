<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessPermission extends Model
{
    use Uuids;
    use HasFactory;
    protected $keyType = 'string';
    protected $fillable = [
        'role_id',
        'permission_id',
        'access_id',
        'status',
        'is_disable'
    ];

    public function roleHasAccess()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function accessHasAccess()
    {
        return $this->belongsTo(Access::class, 'access_id', 'id');
    }

    public function permissionHasAccess()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
    }
}
