<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Payment::with([
            'booking.user:id,name,email',
            'booking.showtime.movie:id,title,poster',
            'booking.showtime.hall.cinema:id,name',
        ]);

        // Regular user only sees their own payments
        if (! $request->user()->isAdmin()) {
            $query->whereHas('booking', fn ($q) => $q->where('user_id', $request->user()->id)
            );
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('method')) {
            $query->where('method', $request->method);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json($payments, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'method' => 'required|in:cash,card,aba,wing,acleda',
            'transaction_id' => 'nullable|string|unique:payments,transaction_id',
        ]);

        $booking = Booking::with([
            'showtime',
            'bookingSeats',
            'payment',
        ])->findOrFail($data['booking_id']);

        // Only booking owner can pay
        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Check booking status
        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => match ($booking->status) {
                    'confirmed' => 'This booking is already paid.',
                    'cancelled' => 'This booking has been cancelled.',
                    'used' => 'This booking has already been used.',
                    default => 'This booking cannot be paid.',
                },
            ], 422);
        }

        // Check booking not expired
        if ($booking->expires_at && now()->gt($booking->expires_at)) {
            $booking->update(['status' => 'cancelled']);

            return response()->json([
                'message' => 'Booking has expired. Please make a new booking.',
            ], 422);
        }

        // Check no existing payment
        if ($booking->payment) {
            return response()->json([
                'message' => 'A payment already exists for this booking.',
            ], 422);
        }

        $payment = null;

        DB::transaction(function () use ($data, $booking, &$payment) {
            // Create payment record
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'transaction_id' => $data['transaction_id'] ?? 'TXN-'.strtoupper(uniqid()),
                'amount' => $booking->total_amount,
                'method' => $data['method'],
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Confirm the booking
            $booking->update(['status' => 'confirmed']);
        });

        return response()->json([
            'message' => 'Payment successful. Booking confirmed.',
            'data' => [
                'payment' => $payment,
                'booking_code' => $booking->booking_code,
                'status' => 'confirmed',
                'amount' => $payment->amount,
                'method' => $payment->method,
                'paid_at' => $payment->paid_at,
            ],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $payment = Payment::with([
            'booking.user:id,name,email,phone',
            'booking.showtime.movie:id,title,slug,poster',
            'booking.showtime.hall.cinema:id,name,address',
            'booking.bookingSeats.seat',
        ])->findOrFail($id);

        // Only owner or admin can view
        if ($payment->booking->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'id' => $payment->id,
            'transaction_id' => $payment->transaction_id,
            'amount' => $payment->amount,
            'method' => $payment->method,
            'status' => $payment->status,
            'paid_at' => $payment->paid_at,
            'booking' => [
                'id' => $payment->booking->id,
                'booking_code' => $payment->booking->booking_code,
                'status' => $payment->booking->status,
                'movie' => $payment->booking->showtime->movie->only([
                    'id', 'title', 'slug', 'poster',
                ]),
                'showtime' => [
                    'date' => $payment->booking->showtime->show_date,
                    'time' => $payment->booking->showtime->show_time,
                    'format' => $payment->booking->showtime->format,
                ],
                'cinema' => $payment->booking->showtime->hall->cinema->only([
                    'id', 'name', 'address',
                ]),
                'seats' => $payment->booking->bookingSeats->map(fn ($bs) => [
                    'seat_code' => $bs->seat->seat_code,
                    'type' => $bs->seat_type,
                    'price' => $bs->price,
                ]),
            ],
        ], 200);
    }

     /**
     * Refund a payment (admin only)
     */
    public function refund(Request $request, string $id)
    {
        $payment = Payment::with('booking')->findOrFail($id);

        if ($payment->status !== 'paid') {
            return response()->json([
                'message' => 'Only paid payments can be refunded.',
            ], 422);
        }

        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'refunded',
            ]);

            $payment->booking->update([
                'status' => 'cancelled',
            ]);
        });

        return response()->json([
            'message' => 'Payment refunded and booking cancelled.',
            'data'    => $payment->fresh(),
        ], 200);
    }


    /**
     * Get payment summary / revenue stats (admin only)
     */
    public function stats(Request $request)
    {
        $query = Payment::where('status', 'paid');

        if ($request->has('date_from')) {
            $query->whereDate('paid_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('paid_at', '<=', $request->date_to);
        }

        $totalRevenue   = (clone $query)->sum('amount');
        $totalPayments  = (clone $query)->count();
        $byMethod       = (clone $query)->selectRaw('method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('method')
            ->get();
        $dailyRevenue   = (clone $query)->selectRaw('DATE(paid_at) as date, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        return response()->json([
            'total_revenue'  => $totalRevenue,
            'total_payments' => $totalPayments,
            'by_method'      => $byMethod,
            'daily_revenue'  => $dailyRevenue,
        ], 200);
    }


    /**
     * Verify payment by booking code (admin — at cinema entrance)
     */
    public function verify(Request $request)
    {
        $request->validate([
            'booking_code' => 'required|string',
        ]);

        $booking = Booking::with([
            'payment',
            'showtime.movie:id,title',
            'showtime.hall.cinema:id,name',
            'bookingSeats.seat',
            'user:id,name,email',
        ])->where('booking_code', $request->booking_code)->firstOrFail();

        $isValid = $booking->status === 'confirmed'
            && $booking->payment
            && $booking->payment->status === 'paid';

        return response()->json([
            'is_valid'     => $isValid,
            'booking_code' => $booking->booking_code,
            'status'       => $booking->status,
            'movie'        => $booking->showtime->movie->title,
            'showtime'     => $booking->showtime->show_date . ' ' . $booking->showtime->show_time,
            'cinema'       => $booking->showtime->hall->cinema->name,
            'seats'        => $booking->bookingSeats->pluck('seat.seat_code'),
            'customer'     => $booking->user->name,
            'paid_at'      => $booking->payment?->paid_at,
        ], 200);
    }
}
