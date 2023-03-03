<?php

namespace App\Http\Controllers;

use App\Services\RoomsService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class RoomsController extends Controller
{
    use ApiResponser;

    /**
     * @var RoomsService
     */
    private RoomsService $roomsService;

    public function __construct(RoomsService $roomsService)
    {
        $this->roomsService = $roomsService;
    }

    /**
     * @return Response
     */
    public function index(): Response
    {
        $rooms = $this->roomsService->getRooms();
        return $this->apiResponse(
            'Rooms successfully fetched',
            Response::HTTP_OK,
            [
                'rooms' => $rooms
            ]
        );
    }

    /**
     * Display a listing of the resource.
     * 
     * @param  Request  $request
     * 
     * @return Response
     */
    public function availability(Request $request): Response
    {
        $validator = Validator::make(
            $request->all(),
            [
                'date' => 'required',
                'startTime' => 'required',
                'duration' => 'required',
                'roomId' => 'required',
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponse(
                'Validation error',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $validator->errors()->getMessages()
            );
        }

        $endTime = date('H:i:s', strtotime($request->get('startTime') . ' +' . $request->get('duration') . ' mins'));

        $isAvailable = $this->roomsService->checkAvailability(
            $request->get('date'),
            $request->get('startTime'),
            $endTime,
            $request->get('roomId'),
            $request->get('reservationId', null),
        );

        return $this->apiResponse(
            'Room availability successfully fetched',
            Response::HTTP_OK,
            [
                'isAvailable' => $isAvailable
            ]
        );
    }
}
