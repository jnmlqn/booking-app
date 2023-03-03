<?php

namespace App\Services;

use App\Repositories\RoomsRepository;

class RoomsService
{
    /**
     *@var string
     */
    private const ROOM_AVAILABLE_FROM = '07:00:00';

    /**
     *@var string
     */
    private const ROOM_AVAILABLE_TO = '16:00:00';

    /**
    * @var RoomsRepository
    */
    private RoomsRepository $roomsRepository;

    public function __construct(RoomsRepository $roomsRepository)
    {
        $this->roomsRepository = $roomsRepository;
    }

    /**
     * @return array
     */
    public function getRooms(): array
    {
        $rooms = $this->roomsRepository->getRooms();
        
        return array_map(
            function($room) {
                return [
                    'id' => $room['id'],
                    'roomName' => $room['name']
                ];
            }, 
            $rooms->toArray()
        );
    }

    /**
     * @param  string  $date
     * @param  string  $startTime
     * @param  string  $endTime
     * @param  string  $roomId
     * @param  string|null  $reservationId
     * 
     * @return bool 
     */
    public function checkAvailability(
        string $date,
        string $startTime,
        string $endTime,
        string $roomId,
        ?string $reservationId = null
    ): bool {
        if (
            $startTime < self::ROOM_AVAILABLE_FROM
            || $endTime > self::ROOM_AVAILABLE_TO
            || ($date . ' ' . $startTime) < date('Y-m-d H:i')
        ) {
            return false;
        }

        return $this->roomsRepository->checkAvailability(
            $date,
            $startTime,
            $endTime,
            $roomId,
            $reservationId
        );
    }
}
