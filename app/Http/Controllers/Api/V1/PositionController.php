<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Position\StorePositionRequest;
use App\Http\Requests\Position\UpdatePositionRequest;
use App\Http\Resources\PositionResource;
use App\Models\Position;
use App\Services\PositionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PositionController extends Controller
{

    public function __construct(
        protected PositionService $positionService
    )
    {

    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PositionResource::collection(
            $this->positionService->getAll()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePositionRequest $request)
    {
        $position = $this->positionService->create($request->validated());

        return response()->json([
            'message'=> 'Position Created successfully',
            'data'=> new PositionResource($position),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position)
    {
        return new PositionResource($position);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePositionRequest $request, Position $position)
    {
        $position = $this->positionService->update($position, $request->validated());

        return response()->json([
            'message' => 'Position updated successfully.',
            'data' => new PositionResource($position)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position)
    {
        $position = $this->positionService->delete($position);

        return response()->json([
            'message'=> 'Position deleted successfully.'
        ]);
    }

    

}
