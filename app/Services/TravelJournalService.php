<?php

namespace App\Services;

use App\Repositories\TravelJournalRepository;
use App\Repositories\TravelRepository;
use Exception;
use Illuminate\Support\Facades\Auth;

class TravelJournalService
{
    protected $repository;
    protected $travelRepository;

    public function __construct(
        TravelJournalRepository $repository,
        TravelRepository $travelRepository
    ) {
        $this->repository = $repository;
        $this->travelRepository = $travelRepository;
    }

    /**
     * Get all journals
     */
    public function getAll($search = null)
    {
        return $this->repository->getAll($search);
    }

    /**
     * Get journals by travel
     */
    public function getByTravel($travelId)
    {
        $travel = $this->travelRepository->findTravelById($travelId);

        return $this->repository->getByTravel($travelId);
    }

    /**
     * Get favorite journals
     */
    public function getFavorites()
    {
        return $this->repository->getFavorites();
    }

    /**
     * Find journal by ID
     */
    public function find($journalId)
    {
        $journal = $this->repository->find($journalId);

        if (!$journal) {
            throw new Exception('Journal not found.');
        }

        return $journal;
    }

    /**
     * Create new journal
     */
    public function createJournal(array $data)
    {
        $payload = [
            'user_id' => Auth::id(),
            'title' => $data['title'],
            'content' => $data['content'],
            'journal_date' => $data['journal_date'] ?? now()->toDateString(),
            'mood' => $data['mood'] ?? null,
            'weather' => $data['weather'] ?? null,
            'location' => $data['location'] ?? null,
            'is_favorite' => $data['is_favorite'] ?? false,
        ];

        // Link to travel if provided
        if (!empty($data['travel_id'])) {
            $travel = $this->travelRepository->findTravelById($data['travel_id']);
            $payload['travel_id'] = $data['travel_id'];
        }

        return $this->repository->create($payload);
    }

    /**
     * Update journal
     */
    public function updateJournal($journalId, array $data)
    {
        $journal = $this->find($journalId);

        // Check ownership
        if ($journal->user_id !== Auth::id()) {
            throw new Exception('You are not authorized to update this journal.');
        }

        $payload = [
            'title' => $data['title'] ?? $journal->title,
            'content' => $data['content'] ?? $journal->content,
            'journal_date' => $data['journal_date'] ?? $journal->journal_date,
            'mood' => $data['mood'] ?? $journal->mood,
            'weather' => $data['weather'] ?? $journal->weather,
            'location' => $data['location'] ?? $journal->location,
        ];

        return $this->repository->update($journal, $payload);
    }

    /**
     * Delete journal
     */
    public function deleteJournal($journalId)
    {
        $journal = $this->find($journalId);

        // Check ownership
        if ($journal->user_id !== Auth::id()) {
            throw new Exception('You are not authorized to delete this journal.');
        }

        return $this->repository->delete($journal);
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite($journalId)
    {
        $journal = $this->find($journalId);

        return $this->repository->toggleFavorite($journal);
    }
}
