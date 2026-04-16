<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Movie::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        // Filter by date via showtimes
        if ($request->has('date')) {
            $query->whereHas('showtimes', fn ($q) => $q->where('show_date', $request->date)
                ->where('status', 'scheduled')
            );
        }

        $movies = $query->latest()->paginate($request->get('per_page', 10));

        // Append show_dates for each movie
        $movies->through(function ($movie) {
            $movie->show_dates = $movie->showtimes()
                ->where('status', 'scheduled')
                ->where('show_date', '>=', now()->toDateString())
                ->pluck('show_date')
                ->unique()
                ->values();

            return $movie;
        });

        return response()->json($movies, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'synopsis' => 'required|string',
            'director' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:50',
            'release_date' => 'nullable|date',
            'rating' => 'nullable|numeric|min:0|max:10',
            'trailer_url' => 'nullable|url',
            'status' => 'required|in:now_showing,coming_soon,ended',
            'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'genres' => 'nullable|array',        // ← array not genre_ids
            'genres.*' => 'string|max:50',
        ]);

        $data['slug'] = Str::slug($request->title);

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('movie/posters', 'public');
        }

        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('movie/banners', 'public');
        }

        $movie = Movie::create($data);

        return response()->json([
            'data' => $movie,
            'massage' => 'Movie created',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        $movie = Movie::where('slug', $slug)->firstOrFail();

        return response()->json($movie, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $movie = Movie::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'synopsis' => 'required|string',
            'director' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:50',
            'release_date' => 'nullable|date',
            'rating' => 'nullable|numeric|min:0|max:10',
            'trailer_url' => 'nullable|url',
            'status' => 'required|in:now_showing,coming_soon,ended',
            'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'genres' => 'nullable|array',        // ← array not genre_ids
            'genres.*' => 'string|max:50',
        ]);

        if ($request->has('title')) {
            $data['slug'] = Str::slug($request->title);
        }

        if ($request->hasFile('poster')) {
            if ($movie->poster) {
                Storage::disk('public')->delete($movie->poster);
            }
            $data['poster'] = $request->file('poster')->store('movie/posters', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($movie->poster) {
                Storage::disk('public')->delete($movie->poster);
            }
            $data['banner'] = $request->file('banner')->store('movie/banners', 'public');
        }

        $movie->update($data);

        return response()->json([
            'data' => $movie,
            'message' => 'Movie updated successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $movie = Movie::findOrFail($id);
        if ($movie->poster) {
            Storage::disk('public')->delete($movie->poster);
        }
        if ($movie->banner) {
            Storage::disk('public')->delete($movie->banner);
        }
        $movie->delete();

    }

    public function showtimes(string $slug)
    {
        $movie = Movie::where('slug', $slug)->firstOrFail();

        $showtimes = $movie->showtimes()
            ->with('hall.cinema')
            ->where('show_date', '>=', now()->toDateString())
            ->where('status', 'scheduled')
            ->orderBy('show_date')
            ->orderBy('show_time')
            ->get()
            ->groupBy('show_date'); // group by date for frontend

        return response()->json([
            'movie' => $movie->only(['id', 'title', 'slug', 'poster']),
            'showtimes' => $showtimes,
        ], 200);
    }
}
