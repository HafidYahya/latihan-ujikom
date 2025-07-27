<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'uom', 'harga_beli', 'harga_jual'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
