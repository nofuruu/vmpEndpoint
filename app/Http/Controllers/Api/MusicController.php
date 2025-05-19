<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\DBAL\TimestampType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MusicController extends Controller
{
    protected $user;
    protected $model;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        try {
            $query = $this->model->orderBy('id', 'asc');
            if ($request->has('from_date') && $request->has('to_date')) {
                $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
            }

            $music = $query->get();
            return $this->successResponse($music);
        } catch (\Exception $e) {
            return $this->errorResponse('Error fetching products', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'album' => 'nullable|string|max:255',
                'genre' => 'nullable|string|max:255',
                'cover_img' => 'required|string|max:255', // jika URL
                'audio_file' => 'required|string|max:255', // jika URL
                'duration' => 'nullable|string|max:255',
                'release_date' => 'nullable|date', // atau 'date_format:Y-m-d' jika butuh format khusus
                'description' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 422);
            }

            $music = $this->model->create($validator->validated());
            return $this->successResponse($music, 'Music successfully added', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error adding music: ' . $e->getMessage(), 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $music = $this->model->findOrFail($id);
            return $this->successResponse($music);
        } catch (\Exception $e) {
            return $this->errorResponse('Music not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $music = $this->model->findOrFail($id);
            $music->delete();
            return $this->successResponse(null, 'Music Deleted', 500);
        } catch (\Exception $e) {
            return $this->errorResponse('Error deleting music', 500);
        }
    }
}
