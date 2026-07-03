<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $npwp
 * @property string|null $address
 * @property string|null $bank_name
 * @property string|null $bank_account_number
 * @property string|null $bank_account_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Vendor extends Model
{
    protected $fillable = [
        'name',
        'npwp',
        'address',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
    ];

    /**
     * @return HasMany<Procurement, $this>
     */
    public function procurements(): HasMany
    {
        return $this->hasMany(Procurement::class);
    }
}
