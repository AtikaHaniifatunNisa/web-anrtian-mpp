<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'services';
    protected $fillable = ['instansi_id', 'name'];

    public function counter()
    {
        return $this->belongsTo(Counter::class, 'service_id');
    }
        // relasi ke Queue
    public function queues()
    {
        return $this->hasMany(\App\Models\Queue::class, 'service_id', 'id');
    }
  
    public function instansi()
    {
        return $this->belongsTo(\App\Models\Instansi::class, 'instansi_id', 'instansi_id');
    }

}