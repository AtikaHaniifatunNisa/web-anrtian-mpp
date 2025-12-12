<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'instansi_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'working_hours',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'string',
        'check_out' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function instansi(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Instansi::class, 'instansi_id', 'instansi_id');
    }
}
