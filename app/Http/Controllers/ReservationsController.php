<?php

namespace App\Http\Controllers;

use App\Exceptions\ReservationsException;
use App\Services\ReservationsService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ReservationsController extends Controller
{
    use ApiResponser;

    /**
     * @var ReservationsService
     */
    private ReservationsService $reservationsService;

    public function __construct(ReservationsService $reservationsService)
    {
        $this->reservationsService = $reservationsService;
    }

    /**
     * Display a listing of the resource.
     * 
     * @param  Request  $request
     *
     * @return Response
     */
    public function index(): Response
    {
        $reservations = $this->reservationsService->getReservations();

        return $this->apiResponse(
            'Reservations list successfully fetched',
            Response::HTTP_OK,
            [
                'reservations' => $reservations
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * 
     * @return Response
     */
    public function store(Request $request): Response
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

        try {
            $reservation = $this->reservationsService->createReservation(
                $request->get('date'),
                $request->get('startTime'),
                $endTime,
                $request->get('roomId'),
            );

            return $this->apiResponse('Room reservation successfully created');
        } catch (ReservationsException $e) {
            return $this->apiResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST,
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  string  $id
     * 
     * @return Response
     */
    public function update(Request $request, string $id): Response
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

        try {
            $reservation = $this->reservationsService->updateReservation(
                $id,
                $request->get('date'),
                $request->get('startTime'),
                $endTime,
                $request->get('roomId'),
            );

            return $this->apiResponse('Room reservation successfully updated');
        } catch (ReservationsException $e) {
            return $this->apiResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST,
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * 
     * @return Response
     */
    public function destroy(string $id): Response
    {
        $this->reservationsService->deleteReservation($id);

        return $this->apiResponse('Reservation successfully deleted');
    }
}
