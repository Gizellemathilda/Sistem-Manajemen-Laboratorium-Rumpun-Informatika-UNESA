<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRuang extends Model
{
    protected $table = 'booking_ruang';

    protected $fillable = [
        'user_id', 'ruangan_id', 'tanggal', 'waktu', 'keperluan', 'status',
    ];

    public function user()    { return $this->belongsTo(User::class); }
    public function ruangan() { return $this->belongsTo(Ruangan::class); }
}
