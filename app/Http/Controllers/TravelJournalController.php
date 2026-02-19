<?php

namespace App\Http\Controllers;

use App\Services\TravelJournalService;
use App\Services\TravelService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TravelJournalController extends Controller
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
        $search = $request->query('search');
        $journals = $this->service->getAll($search);

        return view('journals.index', [
            'journals' => $journals,
            'search' => $search
        ]);
    }

    /**
     * Show journal detail
     */
    public function show($journalId)
    {
        $journal = $this->service->find($journalId);

        return view('journals.show', [
            'journal' => $journal
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $travels = $this->travelService->getAllTravels([]);

        return view('journals.create', [
            'travels' => $travels
        ]);
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

            return redirect()->route('journals.index')->with('success', 'Journal berhasil ditulis!');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Show edit form
     */
    public function edit($journalId)
    {
        $journal = $this->service->find($journalId);
        $travels = $this->travelService->getAllTravels([]);

        // Check ownership for edit
        if ($journal->user_id !== auth()->id()) {
            return redirect()->route('journals.index')->with('error', 'Anda tidak berhak mengedit journal ini.');
        }

        return view('journals.edit', [
            'journal' => $journal,
            'travels' => $travels
        ]);
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

            $this->service->updateJournal($journalId, $request->all());

            DB::commit();

            return redirect()->route('journals.show', $journalId)->with('success', 'Journal berhasil diupdate!');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage())->withInput();
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

            return redirect()->route('journals.index')->with('success', 'Journal berhasil dihapus!');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite($journalId)
    {
        try {
            $journal = $this->service->toggleFavorite($journalId);

            return redirect()->back()->with('success', $journal->is_favorite ? 'Ditandai sebagai favorit!' : 'Dihapus dari favorit!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show journals for a specific travel
     */
    public function byTravel($travelId)
    {
        $travel = $this->travelService->findTravelById($travelId);
        $journals = $this->service->getByTravel($travelId);

        return view('journals.by-travel', [
            'travel' => $travel,
            'journals' => $journals
        ]);
    }
}
