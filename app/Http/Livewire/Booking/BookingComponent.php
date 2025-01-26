<?php

namespace App\Http\Livewire\Booking;

use App\Models\Booking;
use Livewire\Component;

class BookingComponent extends Component
{
    public function render()
    {
        $bookings = Booking::all();
        return view(
            'livewire.booking.booking-component',
            ['bookings' => $bookings]
        );
    }
}
