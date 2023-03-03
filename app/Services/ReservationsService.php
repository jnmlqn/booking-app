<?php

namespace App\Services;

use App\Exceptions\ReservationsException;
use App\Repositories\ReservationsRepository;
use App\Services\RoomsService;

class ReservationsService
{
    /**
     * @var string
     */
    private const STATUS_INCOMING = 'Incoming';

    /**
     * @var string
     */
    private const STATUS_ONGOING = 'Ongoing';

    /**
     * @var string
     */
    private const STATUS_ENDED = 'Ended';

    /**
    * @var ReservationsRepository
    */
    private ReservationsRepository $reservationsRepository;

    /**
    * @var RoomsService
    */
    private RoomsService $roomsService;

    public function __construct(
        ReservationsRepository $reservationsRepository,
        RoomsService $roomsService
    ) {
        $this->reservationsRepository = $reservationsRepository;
        $this->roomsService = $roomsService;
    }

    /**
     * @return array
     */
    public function getReservations(): array
    {
        $reservations = $this->reservationsRepository->getReservations();

        return array_map(
            function ($reservation) {
                $startDate = date('Y-m-d H:i', strtotime($reservation['reservation_date'] . ' ' . $reservation['start_time']));
                $endDate = date('Y-m-d H:i', strtotime($reservation['reservation_date'] . ' ' . $reservation['end_time']));
                
                $status = self::STATUS_INCOMING;

                if ($startDate < date('Y-m-d H:i') && $endDate > date('Y-m-d H:i')) {
                    $status = self::STATUS_ONGOING;
                } elseif ($endDate < date('Y-m-d H:i')) {
                    $status = self::STATUS_ENDED;
                }

                $dateDiff = date_diff(new \DateTime($reservation['end_time']), new \DateTime($reservation['start_time']));

                return [
                    'id' => $reservation['id'],
                    'roomId' => $reservation['room_id'],
                    'roomName' => $reservation['room_name'],
                    'reservationDate' => date('M d, Y', strtotime($reservation['reservation_date'])),
                    'startTime' => date('h:i A', strtotime($reservation['start_time'])),
                    'endTime' => date('h:i A', strtotime($reservation['end_time'])),
                    'duration' => $dateDiff->i,
                    'startDateTime' => $startDate,
                    'status' => $status
                ];
            },
            $reservations->toArray()
        );
    }

    /**
     * @param  string  $date
     * @param  string  $startTime
     * @param  string  $endTime
     * @param  string  $roomId
     * 
     * @return bool
     * @throws ReservationsException
     */
    public function createReservation(
        string $date,
        string $startTime,
        string $endTime,
        string $roomId
    ): bool {
        $isAvailable = $this->roomsService->checkAvailability(
            $date,
            $startTime,
            $endTime,
            $roomId
        );

        if (!$isAvailable) {
            throw new ReservationsException('Room is not available for the given date and time', 1);
        }

        $this->reservationsRepository->createReservation(
            $date,
            $startTime,
            $endTime,
            $roomId
        );

        return true;
    }

    /**
     * @param  string  $id
     * @param  string  $date
     * @param  string  $startTime
     * @param  string  $endTime
     * @param  string  $roomId
     * 
     * @return bool
     * @throws ReservationsException
     */
    public function updateReservation(
        string $id,
        string $date,
        string $startTime,
        string $endTime,
        string $roomId
    ): bool {
        $reservation = $this->reservationsRepository->getReservation($id);

        if (is_null($reservation)) {
            throw new ReservationsException('Reservation not found', 1);
        }

        $startDate = date('Y-m-d H:i', strtotime($reservation['reservation_date'] . ' ' . $reservation['start_time']));
        $endDate = date('Y-m-d H:i', strtotime($reservation['reservation_date'] . ' ' . $reservation['end_time']));
        
        $status = self::STATUS_INCOMING;

        if ($startDate < date('Y-m-d H:i') && $endDate > date('Y-m-d H:i')) {
            $status = self::STATUS_ONGOING;
        } elseif ($endDate < date('Y-m-d H:i')) {
            $status = self::STATUS_ENDED;
        }

        if ($status !== self::STATUS_INCOMING) {
            throw new ReservationsException('Unable to update reservation in this status', 1);
        }

        $isAvailable = $this->roomsService->checkAvailability(
            $date,
            $startTime,
            $endTime,
            $roomId,
            $id
        );

        if (!$isAvailable) {
            throw new ReservationsException('Room is not available for the given date and time', 1);
        }

        $this->reservationsRepository->updateReservation(
            $reservation,
            $date,
            $startTime,
            $endTime,
            $roomId
        );

        return true;
    }

    /**
     * @param  string  $id
     * 
     * @return void
     */
    public function deleteReservation(string $id): void
    {
        $this->reservationsRepository->deleteReservation($id);
    }
}
