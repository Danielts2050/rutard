<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - Ruta Transporte')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="d-flex" style="min-height: 100vh;">
        <nav class="bg-dark text-white" style="width: 250px; flex-shrink: 0;">
            <div class="p-3 border-bottom border-secondary">
                <h5 class="mb-0"><i class="bi bi-truck"></i> Ruta Transporte</h5>
                <small class="text-secondary">Panel Administrativo</small>
            </div>
            <ul class="nav flex-column p-3">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'active fw-bold' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.vehicles') }}" class="nav-link text-white {{ request()->routeIs('admin.vehicles') ? 'active fw-bold' : '' }}">
                        <i class="bi bi-truck"></i> Vehículos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.rutas.activas') }}" class="nav-link text-white {{ request()->routeIs('admin.rutas.activas') ? 'active fw-bold' : '' }}">
                        <i class="bi bi-play-circle"></i> Rutas Activas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.rutas.historial') }}" class="nav-link text-white {{ request()->routeIs('admin.rutas.historial') ? 'active fw-bold' : '' }}">
                        <i class="bi bi-clock-history"></i> Historial
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.alertas.index') }}" class="nav-link text-white {{ request()->routeIs('admin.alertas.*') ? 'active fw-bold' : '' }}">
                        <i class="bi bi-bell"></i> Alertas
                        @php $noLeidas = \App\Models\Alerta::where('leida', false)->count(); @endphp
                        @if ($noLeidas > 0)
                            <span class="badge bg-danger rounded-pill">{{ $noLeidas }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.alertas.config') }}" class="nav-link text-white {{ request()->routeIs('admin.alertas.config') ? 'active fw-bold' : '' }}">
                        <i class="bi bi-gear"></i> Config. Alertas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.exports') }}" class="nav-link text-white {{ request()->routeIs('admin.exports') ? 'active fw-bold' : '' }}">
                        <i class="bi bi-download"></i> Exportar
                    </a>
                </li>
                <hr class="text-secondary">
                <li class="nav-item">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="nav-link text-white btn btn-link border-0 w-100 text-start">
                            <i class="bi bi-box-arrow-left"></i> Cerrar sesión
                        </button>
                    </form>
                </li>
            </ul>
        </nav>

        <main class="flex-grow-1 p-4 bg-light">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
