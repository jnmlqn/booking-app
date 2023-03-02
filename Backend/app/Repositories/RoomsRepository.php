<?php

namespace App\Repositories;

use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Collection;

class RoomsRepository
{
	/**
	 * @return Collection
	 */
	public function getRooms(): Collection
	{
		$rooms = Room::Get();

		return $rooms;
	}

    /**
     * @param  string  $date
     * @param  string  $startTime
     * @param  string  $endTime
     * @param  string  $roomId
     * 
     * @return bool 
     */
    public function checkAvailability(
        string $date,
        string $startTime,
        string $endTime,
        string $roomId
    ): bool {
    	$reservationsCount = Reservation::where('room_id', $roomId)
    		->where('reservation_date', $date)
    		->where(function ($query) use ($startTime, $endTime) {
    			$query->whereBetween('start_time', [$startTime, $endTime])
    				->orWhereBetween('end_time', [$startTime, $endTime])
    				->orWhere(function ($query) use ($startTime, $endTime) {
    					$query->where('start_time', '<', $startTime)
    						->where('end_time', '>', $endTime);
    				});
    		})
    		->count();

    	return $reservationsCount < 1;
    }
}
