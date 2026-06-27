<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Role;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(): View
    {
        $vehicles = Vehicle::with('chofer')->latest()->paginate(10);
        return view('vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        $choferes = Role::where('name', 'Chofer')->first()?->users ?? collect();
        return view('vehicles.create', compact('choferes'));
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $vehicle = Vehicle::create($request->validated());

        return redirect()->route('vehicles.index')
            ->with('success', "Vehículo {$vehicle->placa} creado correctamente.");
    }

    public function show(Vehicle $vehicle): View
    {
        $vehicle->load('chofer');
        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle): View
    {
        $choferes = Role::where('name', 'Chofer')->first()?->users ?? collect();
        return view('vehicles.edit', compact('vehicle', 'choferes'));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($request->validated());

        return redirect()->route('vehicles.index')
            ->with('success', "Vehículo {$vehicle->placa} actualizado correctamente.");
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', "Vehículo eliminado correctamente.");
    }
}
