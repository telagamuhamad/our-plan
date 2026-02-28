<?php

namespace App\Repositories;

use App\Models\TravelPhoto;

class TravelPhotoRepository
{
    protected $model;

    public function __construct(TravelPhoto $model)
    {
        $this->model = $model;
    }

    /**
     * Get all photos for a travel
     */
    public function getByTravel($travelId)
    {
        return $this->model->with('uploader')
            ->byTravel($travelId)
            ->ordered()
            ->get();
    }

    /**
     * Find photo by ID
     */
    public function find($id)
    {
        return $this->model->with('travel', 'uploader')->find($id);
    }

    /**
     * Create new photo
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update photo
     */
    public function update(TravelPhoto $photo, array $data)
    {
        return $photo->update($data);
    }

    /**
     * Delete photo
     */
    public function delete(TravelPhoto $photo)
    {
        return $photo->delete();
    }

    /**
     * Update photo order
     */
    public function updateOrder(array $photoOrders)
    {
        foreach ($photoOrders as $order => $photoId) {
            $this->model->where('id', $photoId)->update(['order' => $order]);
        }
    }

    /**
     * Get photo count for a travel
     */
    public function countByTravel($travelId)
    {
        return $this->model->byTravel($travelId)->count();
    }
}
