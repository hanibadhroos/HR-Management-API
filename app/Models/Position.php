<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use \App\Models\Employee;
class Position extends Model
{

    use HasFactory;

    protected $fillable = ['title', 'description'];

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function booted()
    {
        static::creating(function ($position) {
            if (!$position->id) {
                $position->id = Str::uuid();
            }
        });
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
