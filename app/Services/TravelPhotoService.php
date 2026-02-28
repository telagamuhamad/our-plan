<?php

namespace App\Services;

use App\Repositories\TravelPhotoRepository;
use App\Repositories\TravelRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TravelPhotoService
{
    protected $repository;
    protected $travelRepository;

    public function __construct(
        TravelPhotoRepository $repository,
        TravelRepository $travelRepository
    ) {
        $this->repository = $repository;
        $this->travelRepository = $travelRepository;
    }

    /**
     * Get all photos for a travel
     */
    public function getByTravel($travelId)
    {
        $travel = $this->travelRepository->findTravelById($travelId);

        return $this->repository->getByTravel($travelId);
    }

    /**
     * Upload photo to travel
     */
    public function upload($travelId, $photo, $caption = null)
    {
        $travel = $this->travelRepository->findTravelById($travelId);

        // Validate file
        if (!$photo->isValid()) {
            throw new Exception('File upload failed.');
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($photo->getMimeType(), $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
        }

        // Validate file size (max 5MB)
        if ($photo->getSize() > 5 * 1024 * 1024) {
            throw new Exception('File size exceeds 5MB limit.');
        }

        // Store file
        $path = $photo->store('travel-photos', 'public');

        // Get current max order
        $maxOrder = $this->repository->getByTravel($travelId)->max('order') ?? -1;

        $photoData = [
            'travel_id' => $travelId,
            'uploaded_by' => Auth::id(),
            'photo_path' => $path,
            'caption' => $caption,
            'order' => $maxOrder + 1,
        ];

        return $this->repository->create($photoData);
    }

    /**
     * Upload multiple photos
     */
    public function uploadMultiple($travelId, array $photos)
    {
        $results = [];
        $maxOrder = $this->repository->getByTravel($travelId)->max('order') ?? -1;

        foreach ($photos as $index => $photo) {
            try {
                $uploadedPhoto = $this->upload($travelId, $photo);
                // Update order to maintain sequence
                $uploadedPhoto->update(['order' => $maxOrder + $index + 1]);
                $results[] = [
                    'success' => true,
                    'photo' => $uploadedPhoto,
                ];
            } catch (Exception $e) {
                $results[] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'filename' => $photo->getClientOriginalName(),
                ];
            }
        }

        return $results;
    }

    /**
     * Update photo caption
     */
    public function updateCaption($photoId, $caption)
    {
        $photo = $this->repository->find($photoId);

        if (!$photo) {
            throw new Exception('Photo not found.');
        }

        return $this->repository->update($photo, ['caption' => $caption]);
    }

    /**
     * Delete photo
     */
    public function delete($photoId)
    {
        $photo = $this->repository->find($photoId);

        if (!$photo) {
            throw new Exception('Photo not found.');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($photo->photo_path)) {
            Storage::disk('public')->delete($photo->photo_path);
        }

        return $this->repository->delete($photo);
    }

    /**
     * Update photos order
     */
    public function updateOrder(array $photoOrders)
    {
        return $this->repository->updateOrder($photoOrders);
    }
}
