<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'account_name',
        'slug',
        'template',
        'price',
        'payment_gateway',
        'payment_delay',
        'video_path',
        'is_active',
        'pesalink_account_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'payment_delay' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function pesalinkAccount()
    {
        return $this->belongsTo(PesaLinkAccount::class, 'pesalink_account_id');
    }
}
