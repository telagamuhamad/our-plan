<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TravelPhotoService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TravelPhotoApiController extends Controller
{
    protected $service;

    public function __construct(TravelPhotoService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all photos for a travel
     */
    public function index($travelId)
    {
        try {
            $photos = $this->service->getByTravel($travelId);

            return response()->json([
                'success' => true,
                'data' => $photos
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload single photo to travel
     */
    public function store(Request $request, $travelId)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,gif,webp|max:5120',
            'caption' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $photo = $this->service->upload(
                $travelId,
                $request->file('photo'),
                $request->caption
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil diupload!',
                'data' => $photo
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload multiple photos
     */
    public function storeMultiple(Request $request, $travelId)
    {
        $request->validate([
            'photos' => 'required|array|max:10',
            'photos.*' => 'required|image|mimes:jpeg,png,gif,webp|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $results = $this->service->uploadMultiple($travelId, $request->file('photos'));

            DB::commit();

            $successCount = count(array_filter($results, fn($r) => $r['success']));
            $failedCount = count(array_filter($results, fn($r) => !$r['success']));

            return response()->json([
                'success' => true,
                'message' => "{$successCount} foto berhasil diupload!" . ($failedCount > 0 ? " {$failedCount} gagal." : ''),
                'data' => [
                    'uploaded' => $successCount,
                    'failed' => $failedCount,
                    'results' => $results
                ]
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update photo caption
     */
    public function update(Request $request, $photoId)
    {
        $request->validate([
            'caption' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $photo = $this->service->updateCaption($photoId, $request->caption);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Caption berhasil diupdate!',
                'data' => $photo
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete photo
     */
    public function destroy($photoId)
    {
        try {
            DB::beginTransaction();

            $this->service->delete($photoId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil dihapus!'
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update photos order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'photo_orders' => 'required|array',
            ]);

        try {
            DB::beginTransaction();

            $this->service->updateOrder($request->photo_orders);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Urutan foto berhasil diupdate!'
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
