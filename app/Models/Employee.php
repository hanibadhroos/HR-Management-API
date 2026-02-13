<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use \App\Models\Position;
use \App\Models\EmployeeLog;
use Illuminate\Notifications\Notifiable;

class Employee extends Model
{

    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'salary',
        'position_id',
        'manager_id',
        'is_founder',
        'salary_changed_at'
    ];

    protected $casts = [
        'is_founder' => 'boolean',
        'salary_changed_at' => 'datetime',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function booted()
    {
        static::creating(function ($employee) {
            if (!$employee->id) {
                $employee->id = Str::uuid();
            }
        });
    }

    public function manager()
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function logs()
    {
        return $this->hasMany(EmployeeLog::class);
    }

    //// scope for filtering by name, salary, min salary, max slary.
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['name'] ?? null, fn ($q, $name) =>
            $q->where('name', 'like', "%{$name}%")
        );

        $query->when($filters['salary'] ?? null, fn ($q, $salary) =>
            $q->where('salary', $salary)
        );

        $query->when($filters['salary_min'] ?? null, fn ($q, $min) =>
            $q->where('salary', '>=', $min)
        );

        $query->when($filters['salary_max'] ?? null, fn ($q, $max) =>
            $q->where('salary', '<=', $max)
        );
    }


}
