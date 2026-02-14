<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use \App\Models\Employee;
class EmployeeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'action',
        'description'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
    
    protected $keyType = 'string';
    public $incrementing = false;



    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
