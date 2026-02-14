<?php
namespace App\Services;

use App\Models\Position;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PositionService
{
    public function getAll()
    {
        return Position::paginate(10);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            return Position::create($data);
        });
    }

    public function update(Position $position, array $data)
    {
        return DB::transaction(function () use ($position, $data) {
            $position->update($data);
            return $position;
        });
    }

    public function delete(Position $position)
    {
        if ($position->employees()->exists()) {
            throw ValidationException::withMessages([
                'position' => 'Position cannot be deleted if it has employees.'
            ]);
        }
    
        $position->delete();
    
        return $position;
    }
    
}
