<?php

namespace App\Http\Controllers;

use App\Models\BookingSeat;
use App\Models\Seat;
use App\Models\Showtime;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = Seat::with('hall.cinema');

        if ($request->has('hall_id')) {
            $data->where('hall_id', $request->hall_id);
        }

        if ($request->has('type')) {
            $data->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $data->where('is_active', $request->boolean('is_active'));
        }

        $seats = $data->orderBy('row')->orderBy('number')->paginate($request->get('per_page', 50));

        return response()->json($seats, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $seat = Seat::with('hall.cinema')->findOrFail($id);

        return response()->json($seat, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $seat = Seat::findOrFail($id);

        $data = $request->validate([
            'type' => 'sometimes|in:standard,vip',
            'is_active' => 'sometimes|boolean',
        ]);

        $seat->update($data);

        return response()->json([
            'message' => 'Seat updated successfully',
            'data' => $seat,
        ], 200);
    }

    /**
     * Bulk update seats type in a hall
     * e.g. change rows F, G to VIP
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'hall_id' => 'required|exists:halls,id',
            'seat_ids' => 'required_without:rows|array',
            'seat_ids.*' => 'exists:seats,id',
            'rows' => 'required_without:seat_ids|array',
            'rows.*' => 'string|max:1',
            'type' => 'required|in:standard,vip',
            'is_active' => 'sometimes|boolean',
        ]);

        $query = Seat::where('hall_id', $request->hall_id);

        // Filter by specific seat ids
        if ($request->has('seat_ids')) {
            $query->whereIn('id', $request->seat_ids);
        }

        // Filter by rows e.g. ["F", "G"]
        if ($request->has('rows')) {
            $query->whereIn('row', $request->rows);
        }

        $data = ['type' => $request->type];

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $updated = $query->update($data);

        return response()->json([
            'message' => "$updated seats updated successfully",
            'updated_count' => $updated,
        ], 200);
    }

    /**
     * Toggle a seat active/inactive
     */
    public function toggleActive(string $id)
    {
        $seat = Seat::findOrFail($id);

        // Check no upcoming bookings for this seat
        $hasBooking = BookingSeat::where('seat_id', $id)
            ->whereHas('booking', fn ($q) => $q->whereIn('status', ['pending', 'confirmed']))
            ->exists();

        if ($hasBooking) {
            return response()->json([
                'message' => 'Cannot deactivate seat with active bookings.',
            ], 422);
        }

        $seat->update(['is_active' => ! $seat->is_active]);

        return response()->json([
            'message' => 'Seat '.($seat->is_active ? 'activated' : 'deactivated'),
            'is_active' => $seat->is_active,
        ], 200);
    }

    /**
     * Get seat availability for a specific showtime
     */
    public function availability(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|exists:showtimes,id',
        ]);

        $showtimeId = $request->showtime_id;

        // Get hall from showtime
        $showtime = Showtime::with('hall.seats')->findOrFail($showtimeId);

        // Get booked seat ids for this showtime
        $bookedSeatIds = BookingSeat::whereHas('booking', function ($q) use ($showtimeId) {
            $q->where('showtime_id', $showtimeId)
                ->whereIn('status', ['pending', 'confirmed']);
        })->pluck('seat_id')->toArray();

        // Map seats with availability and group by row
        $seats = $showtime->hall->seats
            ->where('is_active', true)
            ->sortBy(['row', 'number'])
            ->map(function ($seat) use ($bookedSeatIds, $showtime) {
                return [
                    'id' => $seat->id,
                    'row' => $seat->row,
                    'number' => $seat->number,
                    'seat_code' => $seat->seat_code,
                    'type' => $seat->type,
                    'price' => $seat->type === 'vip'
                        ? $showtime->price_vip
                        : $showtime->price_standard,
                    'is_booked' => in_array($seat->id, $bookedSeatIds),
                ];
            })
            ->groupBy('row')
            ->map(fn ($row) => $row->values());

        return response()->json([
            'showtime_id' => $showtimeId,
            'hall' => $showtime->hall->only(['id', 'name', 'type']),
            'price_standard' => $showtime->price_standard,
            'price_vip' => $showtime->price_vip,
            'total_seats' => $showtime->hall->seats->count(),
            'booked_seats' => count($bookedSeatIds),
            'available_seats' => $showtime->hall->seats->count() - count($bookedSeatIds),
            'seats' => $seats,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
