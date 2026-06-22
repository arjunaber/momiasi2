<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketplace_id',
        'product_id',
        'csv_import_id',
        'order_id',
        'transaction_date',
        'period_month',
        'quantity',
        'unit_price',
        'revenue',
        'cogs',
        'advertising_spend',
        'platform_fee',
        'shipping_subsidy',
        'discount',
        'total_cost',
        'profit',
        'profit_margin',
        'customer_city',
        'status',
        'notes',
    ];

    protected $casts = [
        'transaction_date'  => 'date',
        'quantity'          => 'integer',
        'unit_price'        => 'decimal:2',
        'revenue'           => 'decimal:2',
        'cogs'              => 'decimal:2',
        'advertising_spend' => 'decimal:2',
        'platform_fee'      => 'decimal:2',
        'shipping_subsidy'  => 'decimal:2',
        'discount'          => 'decimal:2',
        'total_cost'        => 'decimal:2',
        'profit'            => 'decimal:2',
        'profit_margin'     => 'decimal:2',
    ];

    //   ADD THIS ACCESSOR - Get formatted date
    public function getFormattedDateAttribute()
    {
        if (empty($this->transaction_date)) {
            return '-';
        }

        try {
            return Carbon::parse($this->transaction_date)->format('d M Y');
        } catch (\Exception $e) {
            // If parsing fails, try to clean the date string
            $cleaned = str_replace('T00:00:00', '', $this->transaction_date);
            return Carbon::parse($cleaned)->format('d M Y');
        }
    }

    //   ADD THIS ACCESSOR - Get date in different format (for inputs)
    public function getDateForInputAttribute()
    {
        if (empty($this->transaction_date)) {
            return null;
        }

        try {
            return Carbon::parse($this->transaction_date)->format('Y-m-d');
        } catch (\Exception $e) {
            $cleaned = str_replace('T00:00:00', '', $this->transaction_date);
            return Carbon::parse($cleaned)->format('Y-m-d');
        }
    }

    public function marketplace()
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function csvImport()
    {
        return $this->belongsTo(CsvImport::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByPeriod($query, $p)
    {
        return $query->where('period_month', $p);
    }

    protected static function boot()
    {
        parent::boot();
        static::saving(function (Transaction $tx) {
            if ($tx->transaction_date) {
                try {
                    $tx->period_month = Carbon::parse($tx->transaction_date)->format('Y-m');
                } catch (\Exception $e) {
                    // Try to clean the date first
                    $cleaned = str_replace('T00:00:00', '', $tx->transaction_date);
                    $tx->period_month = Carbon::parse($cleaned)->format('Y-m');
                }
            }
            $tx->total_cost = ($tx->cogs ?? 0) + ($tx->advertising_spend ?? 0)
                + ($tx->platform_fee ?? 0) + ($tx->shipping_subsidy ?? 0) + ($tx->discount ?? 0);
            $tx->profit        = ($tx->revenue ?? 0) - $tx->total_cost;
            $tx->profit_margin = $tx->revenue > 0
                ? round(($tx->profit / $tx->revenue) * 100, 2) : 0;
        });
    }
}
