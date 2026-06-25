<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Student Referral System</title>

    {{-- Tailwind CSS via CDN (replace with npm build later) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>

    {{-- Tabler Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .sidebar { width: 220px; min-width: 220px; }
        .main-content { flex: 1; min-width: 0; }
        .nav-item { transition: background 0.15s, color 0.15s; }
        .nav-item:hover { background: rgba(255,255,255,0.07); }
        .nav-item.active { background: #1E3A5F; }
        .risk-bar-low    { background: #16A34A; }
        .risk-bar-mod    { background: #D97706; }
        .risk-bar-high   { background: #DC2626; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 4px; }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900">

<div class="flex min-h-screen">

    {{-- ── Sidebar ─────────────────────────────────────────── --}}
    <aside class="sidebar bg-[#0F1B2D] flex flex-col fixed top-0 left-0 h-screen z-30">

        {{-- Logo --}}
        <div class="px-5 py-5 border-b border-white/10">
            <p class="text-white text-sm font-medium leading-tight">Student Referral System</p>
            <p class="text-white/40 text-xs mt-0.5">Misamis University</p>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto py-2">

            <p class="px-5 pt-4 pb-1 text-white/30 text-[10px] uppercase tracking-widest">Main</p>

            <a href="{{ route('admin.dashboard') }}"
               class="nav-item flex items-center gap-2.5 px-3 mx-2 py-2.5 rounded-lg text-sm
                      {{ request()->routeIs('admin.dashboard') ? 'active text-white' : 'text-white/60' }}">
                <i class="ti ti-layout-dashboard text-base w-5"></i> Overview
            </a>

            <a href="{{ route('admin.students.index') }}"
               class="nav-item flex items-center gap-2.5 px-3 mx-2 py-2.5 rounded-lg text-sm
                      {{ request()->routeIs('admin.students.*') ? 'active text-white' : 'text-white/60' }}">
                <i class="ti ti-users text-base w-5"></i> Students
            </a>

            <a href="{{ route('admin.referrals.index') }}"
               class="nav-item flex items-center gap-2.5 px-3 mx-2 py-2.5 rounded-lg text-sm
                      {{ request()->routeIs('admin.referrals.*') ? 'active text-white' : 'text-white/60' }}">
                <i class="ti ti-file-text text-base w-5"></i> Referrals
            </a>

            <a href="{{ route('admin.risk.index') }}"
               class="nav-item flex items-center gap-2.5 px-3 mx-2 py-2.5 rounded-lg text-sm
                      {{ request()->routeIs('admin.risk.*') ? 'active text-white' : 'text-white/60' }}">
                <i class="ti ti-alert-triangle text-base w-5"></i> At-Risk Students
            </a>

            <p class="px-5 pt-4 pb-1 text-white/30 text-[10px] uppercase tracking-widest">Management</p>

            <a href="{{ route('admin.users.index') }}"
               class="nav-item flex items-center gap-2.5 px-3 mx-2 py-2.5 rounded-lg text-sm
                      {{ request()->routeIs('admin.users.*') ? 'active text-white' : 'text-white/60' }}">
                <i class="ti ti-user-check text-base w-5"></i> Teachers
            </a>

            <a href="{{ route('admin.seminars.index') }}"
               class="nav-item flex items-center gap-2.5 px-3 mx-2 py-2.5 rounded-lg text-sm
                      {{ request()->routeIs('admin.seminars.*') ? 'active text-white' : 'text-white/60' }}">
                <i class="ti ti-school text-base w-5"></i> Seminars
            </a>

            <a href="#"
               class="nav-item flex items-center gap-2.5 px-3 mx-2 py-2.5 rounded-lg text-sm text-white/60">
                <i class="ti ti-calendar text-base w-5"></i> Attendance
            </a>

            <a href="#"
               class="nav-item flex items-center gap-2.5 px-3 mx-2 py-2.5 rounded-lg text-sm text-white/60">
                <i class="ti ti-message-report text-base w-5"></i> Behavioral Reports
            </a>

            <p class="px-5 pt-4 pb-1 text-white/30 text-[10px] uppercase tracking-widest">System</p>

            <a href="#"
               class="nav-item flex items-center gap-2.5 px-3 mx-2 py-2.5 rounded-lg text-sm text-white/60">
                <i class="ti ti-chart-bar text-base w-5"></i> Analytics
            </a>

            <a href="#"
               class="nav-item flex items-center gap-2.5 px-3 mx-2 py-2.5 rounded-lg text-sm text-white/60">
                <i class="ti ti-message text-base w-5"></i> SMS Logs
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="nav-item flex items-center gap-2.5 px-3 mx-2 py-2.5 rounded-lg text-sm text-white/60">
                <i class="ti ti-user-cog text-base w-5"></i> User Management
            </a>

            <a href="#"
               class="nav-item flex items-center gap-2.5 px-3 mx-2 py-2.5 rounded-lg text-sm text-white/60">
                <i class="ti ti-settings text-base w-5"></i> Settings
            </a>

        </nav>

        {{-- Bottom user --}}
        <div class="px-3 py-3 border-t border-white/10">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-[#1E3A5F] flex items-center justify-center
                            text-[#7BB3F0] text-xs font-medium flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-white text-xs font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-white/40 text-[11px]">Administrator</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="ml-auto">
                    @csrf
                    <button type="submit" class="text-white/40 hover:text-white/80 transition-colors" title="Logout">
                        <i class="ti ti-logout text-base"></i>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    {{-- ── Main content (offset by sidebar width) ─────────── --}}
    <div class="main-content flex flex-col ml-[220px]">

        {{-- Top bar --}}
        <header class="bg-white border-b border-gray-100 px-6 py-3 flex items-center justify-between sticky top-0 z-20">
            <div>
                <h1 class="text-[15px] font-medium text-gray-900">@yield('page-title', 'Overview')</h1>
                <p class="text-xs text-gray-400 mt-0.5">@yield('page-sub', 'S.Y. 2025–2026 · Second Semester')</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs text-gray-400">
                    <i class="ti ti-calendar text-sm align-[-2px] mr-1"></i>
                    {{ now()->format('F d, Y') }}
                </span>

                {{-- Notifications --}}
                <div class="relative cursor-pointer text-gray-400 hover:text-gray-600">
                    <i class="ti ti-bell text-xl"></i>
                    @if(isset($unreadNotifications) && $unreadNotifications > 0)
                        <span class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-red-500 rounded-full
                                     border-2 border-white"></span>
                    @endif
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-6">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm
                            rounded-lg px-4 py-3 flex items-center gap-2">
                    <i class="ti ti-circle-check text-green-600"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm
                            rounded-lg px-4 py-3 flex items-center gap-2">
                    <i class="ti ti-alert-circle text-red-600"></i>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>

    </div>
</div>

@stack('scripts')
</body>
</html>
