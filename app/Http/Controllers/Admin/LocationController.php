<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBuildingRequest;
use App\Http\Requests\Admin\StoreLocationRequest;
use App\Models\Building;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $request->user();

        $buildings = Building::query()
            ->with(['locations' => fn ($query) => $query->orderBy('room_or_area')->orderBy('name')])
            ->withCount('locations')
            ->orderBy('name')
            ->get();

        return view('admin.locations.index', compact('admin', 'buildings'));
    }

    public function storeBuilding(StoreBuildingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $building = Building::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.locations.index')
            ->with('success', "Building \"{$building->name}\" has been added.");
    }

    public function storeLocation(StoreLocationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $building = Building::findOrFail($validated['building_id']);

        $location = Location::create([
            'building_id' => $building->id,
            'building' => $building->name,
            'room_or_area' => $validated['room_or_area'],
            'floor' => $validated['floor'],
            'name' => "{$building->name} — {$validated['room_or_area']}",
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.locations.index')
            ->with('success', "Room \"{$location->room_or_area}\" has been added to {$building->name}.");
    }
}
