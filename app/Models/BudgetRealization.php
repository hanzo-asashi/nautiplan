<?php

namespace App\Models;

use App\Concerns\HasAuditTrail;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
/**
 * @property int $id
 * @property int $activity_budget_id
 * @property int|null $procurement_id
 * @property string $realization_type
 * @property float $amount
 * @property Carbon $realization_date
 * @property string|null $description
 * @property string|null $receipt_number
 *
 * -- Dokumen Pencairan & Serah Terima
 * @property string|null $bast_number
 * @property Carbon|null $bast_date
 * @property string|null $bap_number
 * @property Carbon|null $bap_date
 * @property string|null $ba_penyerahan_number
 * @property Carbon|null $ba_penyerahan_date
 * @property string|null $sp2d_number
 * @property Carbon|null $sp2d_date
 * @property string|null $spp_number
 * @property Carbon|null $spp_date
 * @property string|null $spm_number
 * @property Carbon|null $spm_date
 * @property string|null $sptjb_number
 * @property Carbon|null $sptjb_date
 * @property int|null $verified_by
 * @property Carbon|null $verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
use Illuminate\Support\Carbon;

class BudgetRealization extends Model
{
    use HasAuditTrail;

    protected $fillable = [
        'activity_budget_id',
        'procurement_id',
        'realization_type',
        'amount',
        'realization_date',
        'description',
        'receipt_number',
        'bast_number',
        'bast_date',
        'bap_number',
        'bap_date',
        'ba_penyerahan_number',
        'ba_penyerahan_date',
        'sp2d_number',
        'sp2d_date',
        'spp_number',
        'spp_date',
        'spm_number',
        'spm_date',
        'sptjb_number',
        'sptjb_date',
        'verified_by',
        'verified_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'realization_date' => 'date',
            'bast_date' => 'date',
            'bap_date' => 'date',
            'ba_penyerahan_date' => 'date',
            'sp2d_date' => 'date',
            'spp_date' => 'date',
            'spm_date' => 'date',
            'sptjb_date' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ActivityBudget, $this>
     */
    public function activityBudget(): BelongsTo
    {
        return $this->belongsTo(ActivityBudget::class);
    }

    /**
     * @return BelongsTo<Procurement, $this>
     */
    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    /**
     * @return HasMany<RealizationItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(RealizationItem::class, 'budget_realization_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /*
     |--------------------------------------------------------------------------
     | Backward Compatibility Accessors (Normalized Relations fallback)
     |--------------------------------------------------------------------------
     */

    public function getVendorNameAttribute(): ?string
    {
        return $this->procurement?->vendor?->name;
    }

    public function getVendorNpwpAttribute(): ?string
    {
        return $this->procurement?->vendor?->npwp;
    }

    public function getVendorAddressAttribute(): ?string
    {
        return $this->procurement?->vendor?->address;
    }

    public function getProcurementNumberAttribute(): ?string
    {
        return $this->procurement?->document_number;
    }

    public function getProcurementDateAttribute(): ?CarbonInterface
    {
        return $this->procurement?->document_date;
    }
}
