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

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function booted()
    {
        static::creating(function ($log) {
            if (!$log->id) {
                $log->id = Str::uuid();
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
