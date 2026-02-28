<?php

namespace App\Http\Controllers;

use App\Services\TravelPhotoService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TravelPhotoController extends Controller
{
    protected $service;

    public function __construct(TravelPhotoService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all photos for a travel (AJAX)
     */
    public function index($travelId)
    {
        $photos = $this->service->getByTravel($travelId);

        return response()->json([
            'success' => true,
            'data' => $photos
        ]);
    }

    /**
     * Upload photo to travel
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

            return redirect()->back()->with('success', 'Foto berhasil diupload!');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
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

            return redirect()->back()->with('success', "{$successCount} foto berhasil diupload!");
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
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

            $this->service->updateCaption($photoId, $request->caption);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Caption berhasil diupdate!'
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

            return redirect()->back()->with('success', 'Foto berhasil dihapus!');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
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
