<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CinemaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     $data = Cinema::withCount('hall');

    //     if ($request->has('is_active')) {
    //         $data->where('is_active', $request->boolean('is_active'));
    //     }

    //     if ($request->has('search')) {
    //         $data->where('search', 'like', '%', $request->search.'%');
    //     }

    //     $cinema = $data->orderBy('name')->paginate(10);

    //     return response()->json($cinema, 200);
    // }
    public function index(Request $request)
    {
        $data = Cinema::withCount('halls');

        if ($request->has('is_active')) {
            $data->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $cinemas = $data->orderBy('name')->paginate(10);

        return response()->json($cinemas, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:cinemas,name',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'format'=>'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $data['slug'] = Str::slug($request->name);

        $cinema = Cinema::create($data);

        return response()->json([
            'message' => 'Cinema created successfully',
            'data' => $cinema,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        $cinema = Cinema::where('slug', $slug)
            ->with(['halls' => function ($q) {
                $q->withCount('seats')
                    ->orderBy('name');
            }])
            ->firstOrFail();

        return response()->json($cinema, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cinema = Cinema::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255|unique:cinemas,name,'.$id,
            'address' => 'sometimes|string|max:255',
            'city' => 'nullable|string|max:100',
            'format'=>'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]);

        // Update slug if name changed
        if ($request->has('name')) {
            $data['slug'] = Str::slug($request->name);
        }

        $cinema->update($data);

        return response()->json([
            'message' => 'Cinema updated successfully',
            'data' => $cinema,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cinema = Cinema::findOrFail($id);

        // Prevent deleting if halls have active showtimes
        $hasShowtimes = $cinema->halls()
            ->whereHas('showtimes', fn ($q) => $q->whereIn('status', ['scheduled']))
            ->exists();

        if ($hasShowtimes) {
            return response()->json([
                'message' => 'Cannot delete cinema with scheduled showtimes.',
            ], 422);
        }

        $cinema->delete();

        return response()->json([
            'message' => 'Cinema deleted successfully',
        ], 200);
    }

    /**
     * Toggle cinema active status
     */
    public function toggleActive(string $id)
    {
        $cinema = Cinema::findOrFail($id);
        $cinema->update(['is_active' => ! $cinema->is_active]);

        return response()->json([
            'message' => 'Cinema '.($cinema->is_active ? 'activated' : 'deactivated'),
            'is_active' => $cinema->is_active,
        ], 200);
    }

    /**
     * Get all active cinemas for dropdown
     */
    public function dropdown()
    {
        $cinemas = Cinema::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'address']);

        return response()->json($cinemas, 200);
    }

    /**
     * Get showtimes for a specific cinema grouped by date and movie
     */
    public function showtimes(string $id, Request $request)
    {
        $cinema = Cinema::findOrFail($id);

        $query = $cinema->halls()
            ->with(['showtimes' => function ($q) use ($request) {
                $q->with('movie:id,title,slug,poster,rating,genres')
                    ->where('status', 'scheduled')
                    ->where('show_date', '>=', now()->toDateString());

                if ($request->has('date')) {
                    $q->where('show_date', $request->date);
                }
            }])
            ->where('is_active', true)
            ->get();

        // Flatten and group by date → movie
        $showtimes = collect();
        foreach ($cinema->halls as $hall) {
            foreach ($hall->showtimes as $showtime) {
                $showtimes->push($showtime);
            }
        }

        $grouped = $showtimes->groupBy('show_date')->map(function ($dayShowtimes) {
            return $dayShowtimes->groupBy('movie_id')->map(function ($movieShowtimes) {
                $first = $movieShowtimes->first();

                return [
                    'movie' => $first->movie->only([
                        'id', 'title', 'slug', 'poster', 'rating', 'genres',
                    ]),
                    'times' => $movieShowtimes->map(fn ($s) => [
                        'id' => $s->id,
                        'show_time' => $s->show_time,
                        'format' => $s->format,
                        'price_standard' => $s->price_standard,
                        'price_vip' => $s->price_vip,
                        'hall' => $s->hall->only(['id', 'name', 'type']),
                    ])->values(),
                ];
            })->values();
        });

        return response()->json([
            'cinema' => $cinema->only(['id', 'name', 'address']),
            'showtimes' => $grouped,
        ], 200);
    }
}
