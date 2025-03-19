<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifyStore extends Model
{
    protected $fillable = [
        'shop_name',
        'shop_url',
        'shop_domain',
        'shop_currency',
        'shop_country',
        'shop_owner_fullname',
        'shop_owner_email',
        'access_token',
        'is_active',
        'installed_at',
        'uninstalled_at'
    ];
}
