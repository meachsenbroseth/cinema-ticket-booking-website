<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Seat;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Booking::with([
            'user:id,name,phone',
            'showtime.movie:id,title,slug,poster',
            'showtime.hall.cinema:id,name',
            'bookingSeats.seat',
            'payment',
        ]);

        if ($request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id') && $request->user()->isAdmin()) {
            $query->where('user_id', $request->user_id);
        }

        $bookings = $query->latest()->paginate(10);

        return response()->json($bookings, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'showtime_id' => 'required|exists:showtimes,id',
            'seat_ids' => 'required|array|min:1|max:10',
            'seat_ids.*' => 'exists:seats,id',
        ]);

        $showtime = Showtime::with('hall')->findOrFail($data['showtime_id']);

        // Check showtime is still scheduled
        if ($showtime->status !== 'scheduled') {
            return response()->json([
                'message' => 'This showtime is no longer available.',
            ], 422);
        }

        // Check showtime has not passed
        $showtimeDateTime = $showtime->show_date.' '.$showtime->show_time;
        if (now()->gt($showtimeDateTime)) {
            return response()->json([
                'message' => 'This showtime has already passed.',
            ], 422);
        }

        // Check seats belong to the correct hall
        $seats = Seat::whereIn('id', $data['seat_ids'])
            ->where('hall_id', $showtime->hall_id)
            ->where('is_active', true)
            ->get();

        if ($seats->count() !== count($data['seat_ids'])) {
            return response()->json([
                'message' => 'One or more seats are invalid or inactive.',
            ], 422);
        }

        // Check seats are not already booked
        $alreadyBooked = BookingSeat::whereIn('seat_id', $data['seat_ids'])
            ->whereHas('booking', function ($q) use ($data) {
                $q->where('showtime_id', $data['showtime_id'])
                    ->whereIn('status', ['pending', 'confirmed']);
            })->pluck('seat_id')->toArray();

        if (! empty($alreadyBooked)) {
            $codes = Seat::whereIn('id', $alreadyBooked)->pluck('seat_code')->join(', ');

            return response()->json([
                'message' => 'The following seats are already booked: '.$codes,
                'booked_seats' => $alreadyBooked,
            ], 422);
        }

        // Calculate total amount
        $totalAmount = $seats->sum(function ($seat) use ($showtime) {
            return $seat->type === 'vip'
                ? $showtime->price_vip
                : $showtime->price_standard;
        });

        $booking = null;

        DB::transaction(function () use ($data, $seats, $showtime, $totalAmount, $request, &$booking) {
            // Create booking
            $booking = Booking::create([
                'booking_code' => 'LGD-'.strtoupper(Str::random(8)),
                'user_id' => $request->user()->id,
                'showtime_id' => $data['showtime_id'],
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(15), // 15 min to complete payment
            ]);

            // Create booking seats
            $bookingSeats = $seats->map(fn ($seat) => [
                'booking_id' => $booking->id,
                'seat_id' => $seat->id,
                'seat_type' => $seat->type,
                'price' => $seat->type === 'vip'
                    ? $showtime->price_vip
                    : $showtime->price_standard,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            BookingSeat::insert($bookingSeats);
        });

        return response()->json([
            'message' => 'Booking created successfully. Complete payment within 15 minutes.',
            'data' => $booking->load([
                'showtime.movie:id,title,slug,poster',
                'showtime.hall.cinema:id,name,address',
                'bookingSeats.seat',
            ]),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $booking = Booking::with([
            'user:id,name,email,phone',
            'showtime.movie:id,title,slug,poster,genres',
            'showtime.hall.cinema:id,name,address',
            'bookingSeats.seat',
            'payment',
        ])->findOrFail($id);

        // Only owner or admin can view
        if ($booking->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'status' => $booking->status,
            'total_amount' => $booking->total_amount,
            'expires_at' => $booking->expires_at,  // ✅ ADD THIS LINE
            'movie' => $booking->showtime->movie,
            'showtime' => [
                'date' => $booking->showtime->show_date,
                'time' => $booking->showtime->show_time,
                'format' => $booking->showtime->format,
            ],
            'cinema' => $booking->showtime->hall->cinema,
            'hall' => $booking->showtime->hall->only(['id', 'name', 'type']),
            'seats' => $booking->bookingSeats->map(fn ($bs) => [
                'seat_code' => $bs->seat->seat_code,
                'row' => $bs->seat->row,
                'number' => $bs->seat->number,
                'type' => $bs->seat_type,
                'price' => $bs->price,
            ]),
            'payment' => $booking->payment,
        ], 200);
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        // Only owner or admin can cancel
        if ($booking->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'message' => 'This booking cannot be cancelled.',
            ], 422);
        }

        // Check showtime hasn't started
        $showtime = $booking->showtime;
        $showtimeDateTime = $showtime->show_date.' '.$showtime->show_time;
        if (now()->gt($showtimeDateTime)) {
            return response()->json([
                'message' => 'Cannot cancel a booking after the showtime has started.',
            ], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Booking cancelled successfully.',
        ], 200);
    }

    /**
     * Mark booking as used (admin — scan at entrance)
     */
    public function markUsed(string $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'confirmed') {
            return response()->json([
                'message' => 'Only confirmed bookings can be marked as used.',
            ], 422);
        }

        $booking->update(['status' => 'used']);

        return response()->json([
            'message' => 'Booking marked as used.',
            'data' => $booking,
        ], 200);
    }

    /**
     * Expire pending bookings (run via scheduler)
     */
    public function expirePending()
    {
        $expired = Booking::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'cancelled']);

        return response()->json([
            'message' => "$expired pending bookings expired.",
        ], 200);
    }
}
