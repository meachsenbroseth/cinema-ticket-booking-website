<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use App\Models\Seat;
use App\Models\Cinema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HallController extends Controller
{
    /**
     * List all halls
     */
    public function index(Request $request)
    {
        $query = Hall::with('cinema');

        if ($request->has('cinema_id')) {
            $query->where('cinema_id', $request->cinema_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $halls = $query->paginate($request->get('per_page', 10));

        return response()->json($halls, 200);
    }

    /**
     * Create a new hall and auto-generate seats
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'cinema_id'    => 'required|exists:cinemas,id',
            'name'         => 'required|string|max:100',
            'type'         => 'required|in:standard,imax,4dx,vip',
            'total_rows'   => 'required|integer|min:1|max:26',   // max 26 rows A-Z
            'seats_per_row'=> 'required|integer|min:1|max:30',
            'vip_rows'     => 'nullable|array',    // e.g. ["F", "G"] for VIP rows
            'vip_rows.*'   => 'string|max:1',
            'is_active'    => 'boolean',
        ]);

        // Use DB transaction — if seat generation fails, hall is not saved
        DB::transaction(function () use ($data, $request, &$hall) {
            $hall = Hall::create($data);

            // Auto-generate seats A1, A2... based on total_rows and seats_per_row
            $seats = [];
            $vipRows = $request->get('vip_rows', []);

            for ($r = 0; $r < $data['total_rows']; $r++) {
                $rowLetter = chr(65 + $r); // 65 = ASCII 'A'

                for ($n = 1; $n <= $data['seats_per_row']; $n++) {
                    $seats[] = [
                        'hall_id'    => $hall->id,
                        'row'        => $rowLetter,
                        'number'     => $n,
                        'seat_code'  => $rowLetter . $n,
                        'type'       => in_array($rowLetter, $vipRows) ? 'vip' : 'standard',
                        'is_active'  => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Bulk insert all seats at once
            Seat::insert($seats);
            
        });

        return response()->json([
            'message' => 'Hall created with ' . ($data['total_rows'] * $data['seats_per_row']) . ' seats',
        ], 201);
    }

    /**
     * Show a single hall with its seats
     */
    public function show(string $id)
    {
        $hall = Hall::with(['cinema', 'seats' => function ($q) {
            $q->where('is_active', true)->orderBy('row')->orderBy('number');
        }])->findOrFail($id);

        // Group seats by row for frontend grid
        $seatsByRow = $hall->seats->groupBy('row')->map(function ($seats) {
            return $seats->map(fn($s) => [
                'id'        => $s->id,
                'row'       => $s->row,
                'number'    => $s->number,
                'seat_code' => $s->seat_code,
                'type'      => $s->type,
                'is_active' => $s->is_active,
            ])->values();
        });

        return response()->json([
            'id'           => $hall->id,
            'name'         => $hall->name,
            'type'         => $hall->type,
            'total_rows'   => $hall->total_rows,
            'seats_per_row'=> $hall->seats_per_row,
            'is_active'    => $hall->is_active,
            'cinema'       => $hall->cinema->only(['id', 'name', 'address']),
            'total_seats'  => $hall->seats->count(),
            'seats'        => $seatsByRow,
        ], 200);
    }

    /**
     * Update hall info (not seats)
     */
    public function update(Request $request, string $id)
    {
        $hall = Hall::findOrFail($id);

        $data = $request->validate([
            'name'      => 'sometimes|string|max:100',
            'type'      => 'sometimes|in:standard,imax,4dx,vip',
            'is_active' => 'sometimes|boolean',
        ]);

        // Prevent changing rows/seats_per_row if showtimes exist
        if ($request->hasAny(['total_rows', 'seats_per_row'])) {
            $hasShowtimes = $hall->showtimes()->exists();
            if ($hasShowtimes) {
                return response()->json([
                    'message' => 'Cannot change seat layout — this hall has existing showtimes.',
                ], 422);
            }
        }

        $hall->update($data);

        return response()->json([
            'message' => 'Hall updated successfully',
            'data'    => $hall->load('cinema'),
        ], 200);
    }

    /**
     * Delete a hall
     */
    public function destroy(string $id)
    {
        $hall = Hall::findOrFail($id);

        // Prevent deleting if showtimes exist
        if ($hall->showtimes()->exists()) {
            return response()->json([
                'message' => 'Cannot delete hall with existing showtimes.',
            ], 422);
        }

        // Seats will cascade delete (set up in migration with cascadeOnDelete)
        $hall->delete();

        return response()->json([
            'message' => 'Hall and all its seats deleted successfully',
        ], 200);
    }

    /**
     * Toggle hall active status
     */
    public function toggleActive(string $id)
    {
        $hall = Hall::findOrFail($id);
        $hall->update(['is_active' => !$hall->is_active]);

        return response()->json([
            'message'   => 'Hall ' . ($hall->is_active ? 'activated' : 'deactivated'),
            'is_active' => $hall->is_active,
        ], 200);
    }

    /**
     * Regenerate seats for a hall (use carefully)
     */
    public function regenerateSeats(Request $request, string $id)
    {
        $hall = Hall::findOrFail($id);

        // Block if bookings exist
        $hasBookings = $hall->seats()
            ->whereHas('bookingSeats.booking', fn($q) => $q->whereIn('status', ['confirmed', 'pending']))
            ->exists();

        if ($hasBookings) {
            return response()->json([
                'message' => 'Cannot regenerate seats — active bookings exist.',
            ], 422);
        }

        $request->validate([
            'total_rows'    => 'required|integer|min:1|max:26',
            'seats_per_row' => 'required|integer|min:1|max:30',
            'vip_rows'      => 'nullable|array',
            'vip_rows.*'    => 'string|max:1',
        ]);

        DB::transaction(function () use ($request, $hall) {
            // Delete existing seats
            $hall->seats()->delete();

            // Update hall dimensions
            $hall->update([
                'total_rows'    => $request->total_rows,
                'seats_per_row' => $request->seats_per_row,
            ]);

            // Regenerate seats
            $seats = [];
            $vipRows = $request->get('vip_rows', []);

            for ($r = 0; $r < $request->total_rows; $r++) {
                $rowLetter = chr(65 + $r);
                for ($n = 1; $n <= $request->seats_per_row; $n++) {
                    $seats[] = [
                        'hall_id'    => $hall->id,
                        'row'        => $rowLetter,
                        'number'     => $n,
                        'seat_code'  => $rowLetter . $n,
                        'type'       => in_array($rowLetter, $vipRows) ? 'vip' : 'standard',
                        'is_active'  => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            Seat::insert($seats);
        });

        return response()->json([
            'message'     => 'Seats regenerated successfully',
            'total_seats' => $request->total_rows * $request->seats_per_row,
        ], 200);
    }
}