<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/menu-admin-trash.css') }}">
</head>

<body class="d-flex body-bg">

    @php
        $path = request()->path();
    @endphp

    <!-- Sidebar -->
    <div class="d-flex flex-column flex-shrink-0 sidebar-bg p-3">
        {{-- <img src="{{ url('../img/trash-system/Coin.png') }}" alt="Coin" class="img-fluid logo-img"> --}}
        <div class="d-flex flex-column justify-content-center align-items-center mb-3 mb-md-0 text-decoration-none">
            <div>ระบบจัดการค่าบริการจัดการ</div>
            <h1>ขยะมูลฝอย</h1>
        </div>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li>
                <a href="/showdata" class="nav-link {{ Str::contains($path, 'showdata') ? 'active' : '' }}">
                    รายการสลิปเงินเดือน
                </a>
            </li>

            @php
                $tokenData = null;
                if (session('token')) {
                    $tokenData = json_decode(\Illuminate\Support\Facades\Crypt::decryptString(session('token')), true);
                }
            @endphp

            {{-- เมนูเพิ่มสลิปเงินเดือน: เห็นเฉพาะ manager, approver --}}
            @if ($tokenData && $tokenData['role'] === 'manager')
                <li>
                    <a href="/list_new_slip"
                        class="nav-link {{ Str::contains($path, 'trash_installer') ? 'active' : '' }}">
                        เพิ่มสลิปเงินเดือน
                    </a>
                </li>
            @endif

            {{-- เมนูอนุมัติสลิปเงินเดือน: เห็นเฉพาะ approver --}}
            @if ($tokenData && $tokenData['role'] === 'approver')
                <li>
                    <a href="/approve-slip"
                        class="nav-link {{ Str::contains($path, 'trash_can_installation') ? 'active' : '' }}">
                        อนุมัติสลิปเงินเดือน
                    </a>
                </li>
            @endif
        </ul>

    </div>

    <!-- Main Content -->
    <div class="p-4 flex-grow-1">

        {{-- search bar + Hamburger --}}
        <div class="bg-white my-4 p-2 rounded-3 d-flex align-items-center justify-content-end">
            <button class="btn btn-outline-secondary d-md-none me-2" id="hamburger-btn">
                <i class="bi bi-list fs-3"></i>
            </button>

            <div class="nav-item dropdown">
                <a class="nav-link avatar" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="bi bi-person-circle"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    @if (session('token'))
                        @php
                            $tokenData = json_decode(
                                \Illuminate\Support\Facades\Crypt::decryptString(session('token')),
                                true,
                            );
                        @endphp
                        <li class="dropdown-item-text d-flex align-items-end gap-2">
                            <i class="bi bi-person-circle"></i>
                            <span>{{ $tokenData['name'] }}</span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="m-0 p-0">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    @else
                        <li><a class="dropdown-item" href="/login">Login</a></li>
                        <li><a class="dropdown-item" href="/register">Register</a></li>
                    @endif
                </ul>
            </div>
        </div>

        {{-- Content --}}
        <!-- Mobile -->
        <div class="mobile-only content-trash-bg rounded-3">
            <div class="container py-3">
                <div class="bg-white bg-opacity-75 p-3 rounded-3 shadow-sm">
                    @yield('mobile-content')
                </div>
            </div>
        </div>

        <!-- Desktop -->
        <div class="container-fluid desktop-only">
            <div class="row">
                <div class="col-12">
                    <div class="bg-white bg-opacity-75 p-3 rounded-3 shadow-sm">
                        @yield('desktop-content')
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- JS Sidebar Hamburger --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar-bg');
            const hamburgerBtn = document.getElementById('hamburger-btn');

            // toggle sidebar
            hamburgerBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                document.body.classList.toggle('overlay-active');
            });

            // click outside to close
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !hamburgerBtn.contains(e.target)) {
                        sidebar.classList.remove('active');
                        document.body.classList.remove('overlay-active');
                    }
                }
            });
        });
    </script>

</body>

</html>
