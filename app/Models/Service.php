<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'services';
    protected $fillable = ['instansi_id', 'name', 'prefix', 'padding', 'counter_id', 'is_active'];

    public function counter()
    {
        return $this->belongsTo(Counter::class, 'counter_id');
    }
        // relasi ke Queue
    public function queues()
    {
        return $this->hasMany(Queue::class, 'service_id', 'id');
    }
  
    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'instansi_id');
    }

    // Relasi many-to-many dengan Counter
    public function counters()
    {
        return $this->belongsToMany(Counter::class, 'counter_service', 'service_id', 'counter_id');
    }

}