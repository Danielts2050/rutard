<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\View\View;

class VehicleAdminController extends Controller
{
    public function index(): View
    {
        $vehicles = Vehicle::with('chofer')->latest()->paginate(15);
        return view('admin.vehicles.index', compact('vehicles'));
    }
}
