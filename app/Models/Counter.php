<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Counter extends Model
{
    protected $fillable = [
        'name',
        'instansi_id',
        'service_id',
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

    // Relasi 1:1 dengan Service (satu counter = satu service)
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    // Relasi many-to-many dengan Service (untuk compatibility)
    public function assignedServices()
    {
        return $this->belongsToMany(Service::class, 'counter_service', 'counter_id', 'service_id')
            ->select('services.*');
    }

    public function instansis()
    {
        return $this->hasMany(Instansi::class, 'counter_id', 'id');
    }

    // Relasi ke User (1:1)
    public function user()
    {
        return $this->hasOne(User::class, 'counter_id');
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