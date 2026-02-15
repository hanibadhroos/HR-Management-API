<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use \App\Models\Employee;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EmployeeLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'employee_id',
        'action',
        'description'
    ];

        
    protected $keyType = 'string';
    public $incrementing = false;


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
