<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CpiOrder extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    public const STATUS_WAITING = 'waiting';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';

    protected $fillable = [
        'stream_id',
        'user_id',
        'document_id',
        'status',
        'rev',
    ];

    public function form_control_process()
    {
        return $this->hasManyThrough(FormControlProcess::class, CpiOrderHasControlProcess::class, 'cpi_order_id', 'id', 'id', 'form_control_process_id');
    }

    public function sections()
    {
        return $this->hasManyThrough(Section::class, CpiOrderHasSection::class, 'cpi_order_id', 'id', 'id', 'section_id');
    }

    public function lines()
    {
        return $this->hasManyThrough(Line::class, CpiOrderHasSection::class, 'cpi_order_id', 'id', 'id', 'line_id');
    }

    public function cpi_order_has_standards()
    {
        return $this->hasMany(CpiOrderHasStandard::class, 'cpi_order_id', 'id');
    }

    public function cpi_order_has_sections()
    {
        return $this->hasMany(CpiOrderHasSection::class, 'cpi_order_id', 'id');
    }

    public function control_process_standards()
    {
        return $this->hasManyThrough(ControlProcessStandard::class, CpiOrderHasStandard::class, 'cpi_order_id', 'id', 'id', 'control_process_standard_id');
    }

    public function cpi_order_exits()
    {
        return $this->hasOne(CpiOrderExit::class, 'cpi_order_id', 'id');
    }

    public function problems()
    {
        return $this->hasManyThrough(Problem::class, CpiOrderHasProblem::class, 'cpi_order_id', 'id', 'id', 'problem_id');
    }

    public function samplings()
    {
        return $this->hasManyThrough(Sampling::class, CpiOrderHasSampling::class, 'cpi_order_id', 'id', 'id', 'sampling_id');
    }

    public function streams()
    {
        return $this->belongsTo(Stream::class, 'stream_id', 'id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function documents()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }

    public function cpi_order_has_control_process()
    {
        return $this->hasMany(CpiOrderHasControlProcess::class, 'cpi_order_id', 'id');
    }

    public function section_approvals()
    {
        return $this->hasMany(SectionApproval::class, 'cpi_order_id', 'id');
    }

    public function verification_approvals()
    {
        return $this->hasMany(VerificationApproval::class, 'cpi_order_id', 'id');
    }

    public function is_cpi_order_corrected_before()
    {
        return $this->hasMany(IsCpiOrderCorrected::class, 'cpi_order_before_id', 'id');
    }
    public function is_cpi_order_corrected_after()
    {
        return $this->hasMany(IsCpiOrderCorrected::class, 'cpi_order_after_id', 'id');
    }

    public function notifications()
    {
        return $this->hasOne(Notification::class, 'cpi_order_id', 'id');
    }
}
