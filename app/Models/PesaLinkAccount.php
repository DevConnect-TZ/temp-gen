<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesaLinkAccount extends Model
{
    protected $fillable = [
        'name',
        'api_key',
        'base_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function pages()
    {
        return $this->hasMany(Page::class, 'pesalink_account_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'pesalink_account_id');
    }
}
