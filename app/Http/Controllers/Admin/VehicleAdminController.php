<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleAdminController extends Controller
{
    public function index(): View
    {
        $vehicles = Vehicle::with('chofer')->latest()->paginate(15);
        $choferes = User::whereHas('role', fn($q) => $q->where('name', 'Chofer'))->get();
        return view('admin.vehicles.index', compact('vehicles', 'choferes'));
    }

    public function assign(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate(['chofer_id' => 'nullable|exists:users,id']);

        $vehicle->update(['chofer_id' => $data['chofer_id']]);

        $msg = $data['chofer_id']
            ? "Vehículo {$vehicle->placa} asignado a chofer correctamente."
            : "Vehículo {$vehicle->placa} desasignado.";

        return back()->with('success', $msg);
    }
}
