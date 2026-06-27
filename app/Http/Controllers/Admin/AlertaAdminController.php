<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertaAdminController extends Controller
{
    public function index(Request $request): View
    {
        $query = Alerta::with('user', 'ruta', 'vehicle')
            ->latest('fecha_alerta');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->boolean('no_leidas')) {
            $query->where('leida', false);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('titulo', 'like', "%{$q}%")
                    ->orWhere('mensaje', 'like', "%{$q}%");
            });
        }

        $alertas = $query->paginate(20)->withQueryString();

        $tipos = Alerta::select('tipo')->distinct()->pluck('tipo');

        return view('admin.alertas.index', compact('alertas', 'tipos'));
    }

    public function marcarLeida(Alerta $alerta): RedirectResponse
    {
        $alerta->update(['leida' => true]);

        return redirect()->back()->with('success', 'Alerta marcada como leída.');
    }

    public function marcarTodasLeidas(): RedirectResponse
    {
        Alerta::where('leida', false)->update(['leida' => true]);

        return redirect()->back()->with('success', 'Todas las alertas marcadas como leídas.');
    }

    public function destroy(Alerta $alerta): RedirectResponse
    {
        $alerta->delete();

        return redirect()->back()->with('success', 'Alerta eliminada.');
    }
}
