<?php

namespace App\Http\Controllers;

use App\Models\BookingSeat;
use App\Models\Hall;
use App\Models\Movie;
use App\Models\Seat;
use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShowtimeController extends Controller
{
    /**
     * List all showtimes (with filters)
     */
    public function index(Request $request)
    {
        $query = Showtime::with(['movie', 'hall.cinema']);

        if ($request->has('movie_id')) {
            $query->where('movie_id', $request->movie_id);
        }

        if ($request->has('cinema_id')) {
            $query->whereHas('hall', fn ($q) => $q->where('cinema_id', $request->cinema_id));
        }

        if ($request->has('date')) {
            $query->where('show_date', $request->date);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $showtimes = $query->orderBy('show_date')->orderBy('show_time')->paginate(10);

        return response()->json($showtimes, 200);
    }

    /**
     * Create a new showtime
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'hall_id' => 'required|exists:halls,id',
            'show_date' => 'required|date|after_or_equal:today',
            'show_time' => 'required|date_format:H:i',
            'format' => 'required|in:2d,imax,4dx,3d',
            'price_standard' => 'required|numeric|min:0',
            'price_vip' => 'required|numeric|min:0',
            'status' => 'sometimes|in:scheduled,cancelled,completed',
        ]);

        // Check no overlapping showtime in same hall
        $conflict = Showtime::where('hall_id', $request->hall_id)
            ->where('show_date', $request->show_date)
            ->where('show_time', $request->show_time)
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'A showtime already exists in this hall at the same date and time.',
            ], 422);
        }

        $showtime = Showtime::create($data);

        return response()->json([
            'message' => 'Showtime created successfully',
            'data' => $showtime->load(['movie', 'hall.cinema']),
        ], 201);
    }

    /**
     * Show a single showtime with seat availability
     */
    public function show(string $id)
    {
        $showtime = Showtime::with(['movie', 'hall.cinema', 'hall.seats'])
            ->findOrFail($id);

        // Get booked seat IDs for this showtime
        $bookedSeatIds = BookingSeat::whereHas('booking', function ($q) use ($id) {
            $q->where('showtime_id', $id)
                ->whereIn('status', ['pending', 'confirmed']);
        })->pluck('seat_id')->toArray();

        // Map seats with availability
        $seats = $showtime->hall->seats->map(function ($seat) use ($bookedSeatIds) {
            return [
                'id' => $seat->id,
                'row' => $seat->row,
                'number' => $seat->number,
                'seat_code' => $seat->seat_code,
                'type' => $seat->type,
                'is_booked' => in_array($seat->id, $bookedSeatIds),
            ];
        })->groupBy('row'); // group by row for frontend grid

        return response()->json([
            'showtime' => [
                'id' => $showtime->id,
                'show_date' => $showtime->show_date,
                'show_time' => $showtime->show_time,
                'format' => $showtime->format,
                'price_standard' => $showtime->price_standard,
                'price_vip' => $showtime->price_vip,
                'status' => $showtime->status,
            ],
            'movie' => $showtime->movie->only(['id', 'title', 'slug', 'poster']),
            'hall' => [
                'id' => $showtime->hall->id,
                'name' => $showtime->hall->name,
                'type' => $showtime->hall->type,
                'cinema' => $showtime->hall->cinema->only(['id', 'name', 'address']),
            ],
            'seats' => $seats,
        ], 200);
    }

    /**
     * Update a showtime
     */
    public function update(Request $request, string $id)
    {
        $showtime = Showtime::findOrFail($id);

        $data = $request->validate([
            'movie_id' => 'sometimes|exists:movies,id',
            'hall_id' => 'sometimes|exists:halls,id',
            'show_date' => 'sometimes|date',
            'show_time' => 'sometimes|date_format:H:i',
            'format' => 'sometimes|in:2d,imax,4dx,3d',
            'price_standard' => 'sometimes|numeric|min:0',
            'price_vip' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:scheduled,cancelled,completed',
        ]);

        $showtime->update($data);

        return response()->json([
            'message' => 'Showtime updated successfully',
            'data' => $showtime->load(['movie', 'hall.cinema']),
        ], 200);
    }

    /**
     * Delete a showtime
     */
    public function destroy(string $id)
    {
        $showtime = Showtime::findOrFail($id);

        // Prevent deleting if there are confirmed bookings
        $hasBookings = $showtime->bookings()
            ->whereIn('status', ['confirmed', 'pending'])
            ->exists();

        if ($hasBookings) {
            return response()->json([
                'message' => 'Cannot delete showtime with active bookings.',
            ], 422);
        }

        $showtime->delete();

        return response()->json([
            'message' => 'Showtime deleted successfully',
        ], 200);
    }

    /**
     * Get showtimes grouped by date for a specific movie
     * Used by frontend date picker
     */
    /**
     * Get showtimes grouped by date for a specific movie by slug
     */
    public function byMovie(string $slug, Request $request)
    {
        $movie = Movie::where('slug', $slug)
            ->orWhere('id', $slug) // fallback to id
            ->firstOrFail();

        $query = Showtime::with('hall.cinema')
            ->where('movie_id', $movie->id)
            ->where('show_date', '>=', now()->toDateString())
            ->where('status', 'scheduled')
            ->orderBy('show_date')
            ->orderBy('show_time');

        // Optional cinema filter
        if ($request->has('cinema_id')) {
            $query->whereHas('hall', fn ($q) => $q->where('cinema_id', $request->cinema_id)
            );
        }

        // Group by date → cinema
        $grouped = $query->get()
            ->groupBy('show_date')
            ->map(function ($dayShowtimes) {
                return $dayShowtimes
                    ->groupBy(fn ($s) => $s->hall->cinema->id)
                    ->map(function ($cinemaShowtimes) {
                        $first = $cinemaShowtimes->first();

                        return [
                            'cinema' => [
                                'id' => $first->hall->cinema->id,
                                'name' => $first->hall->cinema->name,
                                'address' => $first->hall->cinema->address,
                            ],
                            'hall' => [
                                'id' => $first->hall->id,
                                'name' => $first->hall->name,
                                'type' => $first->hall->type,
                            ],
                            'format' => $first->format,
                            'price_standard' => $first->price_standard,
                            'price_vip' => $first->price_vip,
                            'times' => $cinemaShowtimes->map(fn ($s) => [
                                'id' => $s->id,
                                'show_time' => Carbon::parse($s->show_time)
                                    ->format('g:i A'), // "7:00 PM"
                            ])->values(),
                        ];
                    })->values();
            });

        return response()->json([
            'movie' => $movie->only(['id', 'title', 'slug', 'poster']),
            'showtimes' => $grouped,
        ], 200);
    }
}
