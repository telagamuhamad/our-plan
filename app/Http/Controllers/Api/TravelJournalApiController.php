<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TravelJournalService;
use App\Services\TravelService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TravelJournalApiController extends Controller
{
    protected $service;
    protected $travelService;

    public function __construct(TravelJournalService $service, TravelService $travelService)
    {
        $this->service = $service;
        $this->travelService = $travelService;
    }

    /**
     * Display all journals
     */
    public function index(Request $request)
    {
        try {
            $search = $request->query('search');
            $journals = $this->service->getAll($search);

            return response()->json([
                'success' => true,
                'data' => $journals
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show journal detail
     */
    public function show($journalId)
    {
        try {
            $journal = $this->service->find($journalId);

            return response()->json([
                'success' => true,
                'data' => $journal
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Store new journal
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'journal_date' => 'nullable|date',
            'travel_id' => 'nullable|exists:travels,id',
            'mood' => 'nullable|in:happy,excited,love,sad,tired,adventurous,relaxed,surprised',
            'weather' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'is_favorite' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $journal = $this->service->createJournal($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Journal berhasil ditulis!',
                'data' => $journal
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
     * Update journal
     */
    public function update(Request $request, $journalId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'journal_date' => 'nullable|date',
            'travel_id' => 'nullable|exists:travels,id',
            'mood' => 'nullable|in:happy,excited,love,sad,tired,adventurous,relaxed,surprised',
            'weather' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $journal = $this->service->updateJournal($journalId, $request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Journal berhasil diupdate!',
                'data' => $journal
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
     * Delete journal
     */
    public function destroy($journalId)
    {
        try {
            DB::beginTransaction();

            $this->service->deleteJournal($journalId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Journal berhasil dihapus!'
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
     * Toggle favorite status
     */
    public function toggleFavorite($journalId)
    {
        try {
            $journal = $this->service->toggleFavorite($journalId);

            return response()->json([
                'success' => true,
                'message' => $journal->is_favorite ? 'Ditandai sebagai favorit!' : 'Dihapus dari favorit!',
                'data' => $journal
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show journals for a specific travel
     */
    public function byTravel($travelId)
    {
        try {
            $travel = $this->travelService->findTravelById($travelId);
            $journals = $this->service->getByTravel($travelId);

            return response()->json([
                'success' => true,
                'data' => [
                    'travel' => $travel,
                    'journals' => $journals
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
