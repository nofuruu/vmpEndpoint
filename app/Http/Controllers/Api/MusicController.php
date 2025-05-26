<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\DBAL\TimestampType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Cloudinary\Cloudinary;
use App\Models\Music;

class MusicController extends Controller
{
    protected $user;
    protected $model;
    protected $cloudinary;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => ['secure' => true],
        ]);
        $this->model = new Music();
    }
    public function index(Request $request)
    {
        $query = Music::select(
            'id',
            'title',
            'artist',
            'album',
            'genre',
            'cover_url',
            'audio_url',
            'duration',
            'description',
            'created_at'
        )->orderBy('title');

        // Filter by genre jika ada parameter genre
        if ($request->has('genre') && $request->genre !== null && $request->genre !== '') {
            $query->where('genre', $request->genre);
        }

        $songs = $query->get();

        return response()->json($songs);
    }


    public function datatable(Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get('start');
            $length = $request->get('length');
            $searchValue = $request->input('search.value');

            $query = Music::query();

            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('title', 'like', '%' . $searchValue . '%')
                        ->orWhere('artist', 'like', '%' . $searchValue . '%');
                });
            }

            if ($request->has('from_date') && $request->has('to_date')) {
                $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
            }

            $totalRecords = Music::count();
            $totalFiltered = $query->count();

            $data = $query->orderBy('id', 'asc')
                ->skip($start)
                ->take($length)
                ->get()
                ->map(function ($item) {
                    // Add action buttons to each row
                    $item->action = '<button class="btn btn-danger btn-sm delete-btn" data-id="' . $item->id . '" onclick="deleteMusic(' . $item->id . ')">
                                    <i class="fas fa-trash"></i>
                                </button>';
                    return $item;
                });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uploadCover(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cover_img' => 'required|image|max:5120', // max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $upload = $this->cloudinary->uploadApi()->upload(
                $request->file('cover_img')->getRealPath(),
                ['folder' => 'musikku/covers']
            );

            return response()->json([
                'success' => true,
                'url'     => $upload['secure_url'],
                'public_id' => $upload['public_id'],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function uploadAudio(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'audio_file' => 'required|mimetypes:audio/mpeg,audio/wav,audio/ogg|max:20480', // max 20MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $upload = $this->cloudinary->uploadApi()->upload(
                $request->file('audio_file')->getRealPath(),
                [
                    'resource_type' => 'video', // untuk file audio
                    'folder'        => 'musikku/audios'
                ]
            );

            return response()->json([
                'success' => true,
                'url'     => $upload['secure_url'],
                'public_id' => $upload['public_id'],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload audio gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'album' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'artist' => 'nullable|string|max:255',
            'duration' => 'required|string|max:255',
            'cover_url' => 'required|url',
            'audio_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $music = Music::create([
                'title' => $request->title,
                'album' => $request->album,
                'genre' => $request->genre,
                'artist' => $request->artist,
                'duration' => $request->duration,
                'description' => $request->description,
                'cover_url' => $request->cover_url,
                'audio_url' => $request->audio_url,
            ]);

            return response()->json([
                'success' => true,
                'data' => $music,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
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
    public function destroy(string $id)
    {
        try {
            $music = $this->model->findOrFail($id);
            $music->delete();

            return response()->json([
                'status' => true,
                'message' => 'Music Deleted'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting music: ' . $e->getMessage()
            ], 500);
        }
    }
}
