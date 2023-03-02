<?php

namespace App\Repositories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Collection;

class ReservationsRepository
{
    /**
     * @return Collection
     */
    public function getReservations(): Collection
    {
    	$user = auth()->user();

        $reservations = Reservation::select(
        		'reservations.*',
        		'rooms.name as room_name',
        	)
        	->where('user_id', $user->id)
        	->leftJoin('rooms', 'rooms.id', '=', 'reservations.room_id')
        	->get();

        return $reservations;
    }

    /**
     * @param  string $id
     * 
     * @return Collection
     */
    public function getReservation(string $id): Reservation
    {
    	$user = auth()->user();

        $reservation = Reservation::select(
        		'reservations.*',
        		'rooms.name as room_name',
        	)
        	->where('user_id', $user->id)
        	->where('reservations.id', $id)
        	->leftJoin('rooms', 'rooms.id', '=', 'reservations.room_id')
        	->first();

        return $reservation;
    }

    /**
     * @param  string  $date
     * @param  string  $startTime
     * @param  string  $endTime
     * @param  string  $roomId
     * 
     * @return void 
     */
    public function createReservation(
        string $date,
        string $startTime,
        string $endTime,
        string $roomId
    ): void {
    	$user = auth()->user();

    	Reservation::create([
    		'room_id' => $roomId,
    		'user_id' => $user->id,
    		'reservation_date' => $date,
    		'start_time' => $startTime,
    		'end_time' => $endTime
    	]);
    }

    /**
     * @param  Reservation  $reservation
     * @param  string  $date
     * @param  string  $startTime
     * @param  string  $endTime
     * @param  string  $roomId
     * 
     * @return void
     */
    public function updateReservation(
        Reservation $reservation,
        string $date,
        string $startTime,
        string $endTime,
        string $roomId
    ): void {
    	$reservation->reservation_date = $date;
    	$reservation->start_time = $startTime;
    	$reservation->end_time = $endTime;
    	$reservation->room_id = $roomId;
    	$reservation->save();
    }

    /**
     * @param  string  $id
     * 
     * @return void
     */
    public function deleteReservation(string $id): void
    {
    	$user = auth()->user();

    	Reservation::where('user_id', $user->id)
    		->where('id', $id)
    		->delete();
    }
}
