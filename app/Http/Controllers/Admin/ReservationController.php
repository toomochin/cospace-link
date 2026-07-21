<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['user', 'reservable'])
            ->orderBy('start_time', 'desc')
            ->paginate(20);

        return view('admin.reservations.index', compact('reservations'));
    }
}