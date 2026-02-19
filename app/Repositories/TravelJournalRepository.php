<?php

namespace App\Repositories;

use App\Models\TravelJournal;

class TravelJournalRepository
{
    protected $model;

    public function __construct(TravelJournal $model)
    {
        $this->model = $model;
    }

    /**
     * Get all journals
     */
    public function getAll($search = null)
    {
        $query = $this->model->with('user', 'travel')->search($search);

        return $query->orderByDate()->get();
    }

    /**
     * Get journals by travel
     */
    public function getByTravel($travelId)
    {
        return $this->model->with('user')
            ->byTravel($travelId)
            ->orderByDate()
            ->get();
    }

    /**
     * Get favorite journals
     */
    public function getFavorites()
    {
        return $this->model->with('user', 'travel')
            ->favorites()
            ->orderByDate()
            ->get();
    }

    /**
     * Find journal by ID
     */
    public function find($id)
    {
        return $this->model->with('user', 'travel')->find($id);
    }

    /**
     * Create new journal
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update journal
     */
    public function update(TravelJournal $journal, array $data)
    {
        return $journal->update($data);
    }

    /**
     * Delete journal
     */
    public function delete(TravelJournal $journal)
    {
        return $journal->delete();
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite(TravelJournal $journal)
    {
        $journal->is_favorite = !$journal->is_favorite;
        $journal->save();

        return $journal;
    }

    /**
     * Get journal count by travel
     */
    public function countByTravel($travelId)
    {
        return $this->model->byTravel($travelId)->count();
    }
}
