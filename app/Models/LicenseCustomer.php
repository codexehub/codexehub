<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LicenseCustomer extends Model
{
    use Auditable, HasFactory;

    protected $fillable = ['customer_id', 'name', 'email', 'phone', 'notes'];

    /**
     * The canonical customer this licensing identity belongs to (CUST-001).
     * Nullable only for rows created before the fold-in migration backfilled it.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }
}
