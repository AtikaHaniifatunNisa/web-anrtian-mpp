<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Counter extends Model
{
    protected $fillable = [
        'name',
        'service_id',
        'instansi_id',
        'is_active',
    ];

    protected static function booted()
    {
        static::addGlobalScope('roleBasedAccess', function (Builder $builder) {
            if (Auth::check() && Auth::user()->role === 'operator') {
                $builder->where('id', Auth::user()->counter_id);
            }
        });
    }

    // Relasi ke tabel Instansi
    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'instansi_id');
    }

    public function instansis()
    {
        return $this->hasMany(Instansi::class, 'counter_id', 'id');
    }

    // Relasi ke tabel Service
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'service_id');
    }

    // Relasi ke tabel Queue
    public function queues()
    {
        return $this->hasMany(Queue::class, 'counter_id');
    }


    // Queue yang aktif (sedang dilayani)
    public function activeQueue()
    {
        return $this->hasOne(Queue::class)
            ->whereIn('status', ['waiting', 'serving']);
    }

    // Queue berikutnya (relasi, supaya bisa eager load dengan with())
    public function nextQueue()
    {
        return $this->hasOne(Queue::class)
            ->where('status', 'waiting')
            ->whereNull('counter_id')
            ->whereNull('called_at')
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'asc');
    }

    // Apakah loket masih tersedia
    public function getIsAvailableAttribute()
    {
        $hasServingQueue = $this->queues()
            ->where('status', 'serving')
            ->exists();

        return !$hasServingQueue && $this->is_active;
    }
}