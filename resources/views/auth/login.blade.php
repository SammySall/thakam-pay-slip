    <!DOCTYPE html>
    <html lang="th">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>เข้าสู่ระบบ</title>

        {{-- Bootstrap --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

        <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    </head>

    <body>
        <div class="d-flex justify-content-center align-items-center vh-100">
            <div class="login-card text-center px-5 py-3">

                {{-- โลโก้--}}
                <div class="header-text mb-4">
                    <img src="{{ asset('../../img/Login/Text.png') }}" alt="เข้าสู่ระบบ" class="img-fluid w-75">
                </div>
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- ชื่อผู้ใช้งาน --}}
                    <div class="mb-3 text-start">
                        <div class="input-group">
                            <label for="username" class="input-group-text "><i class="bi bi-person"></i></label>
                            <input type="text" name="username" class="form-control" placeholder="อีเมล"
                                id='username' required>
                        </div>
                    </div>

                    {{-- รหัสผ่าน --}}
                    <div class="mb-3 text-start">
                        <div class="input-group">
                            <label for="password" class="input-group-text"><i class="bi bi-lock"></i></label>
                            <input type="password" name="password" class="form-control" id="password"
                                placeholder="รหัสผ่าน" required>
                            <button type="button" class="btn btn-outline-light " id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    {{-- ยังไม่มีรหัสผ่าน --}}
                    <div class="d-flex justify-content-end text-white-50">
                        <a href="/register" class="text-info text-decoration-none">สมัครสมาชิก</a>
                    </div>

                    {{-- ปุ่มล็อกอิน --}}
                    <div class="my-2">
                        <button type="submit" class="btn-login-img">
                            <img src="{{ asset('../../img/Login/Login-Button.png') }}" alt="ปุ่มเข้าสู่ระบบ"
                                class="img-fluid w-100">
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <script>
            // toggle password visibility
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');
            togglePassword.addEventListener('click', () => {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                togglePassword.innerHTML = type === 'password' ?
                    '<i class="bi bi-eye"></i>' :
                    '<i class="bi bi-eye-slash"></i>';
            });
        </script>
    </body>

    </html>
