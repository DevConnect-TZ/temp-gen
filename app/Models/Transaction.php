<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'page_id',
        'order_id',
        'reference',
        'buyer_email',
        'buyer_name',
        'buyer_phone',
        'amount',
        'currency',
        'gateway',
        'payment_status',
        'transaction_id',
        'channel',
        'msisdn',
        'response_data',
        'completed_at',
        'pesalink_account_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'response_data' => 'json',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the page that this transaction belongs to.
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get the PesaLink account that this transaction belongs to.
     */
    public function pesalinkAccount()
    {
        return $this->belongsTo(PesaLinkAccount::class, 'pesalink_account_id');
    }
}
