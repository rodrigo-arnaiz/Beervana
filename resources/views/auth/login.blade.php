<!doctype html>
<html lang="en">

<head>
    <title>Beervana</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="{{ asset('assets/estilos.css') }}">
</head>

<body>
    <section class="gradient-form no-margin">
        <div class="container login-container py-1 no-margin" style="max-width: 700px;">
            <div class="row d-flex justify-content-center no-margin">
                <div class="col-12 no-margin">
                    <div class="card login-card rounded-3 no-margin" style="width: 100%;">
                        <div class="row g-0 no-margin">
                            <!-- Columna izquierda (formulario) -->
                            <div class="col-lg-6 no-margin">
                                <div class="card-body login-body p-3">
                                    <div class="text-center">
                                        <img src="{{ asset('assets/logo.jpeg') }}" class="logo-img" alt="logo">
                                        <h4 class="login-title">Iniciar Sesión</h4>
                                    </div>

                                    @if (session('error'))
                                        <div class="alert alert-danger mt-2">
                                            {{ session('error') }}
                                        </div>
                                    @endif
                                    @if ($errors->any())
                                        <div class="alert alert-danger mt-2">
                                            @foreach ($errors->all() as $error)
                                                <div>{{ $error }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                    <form class="login-form" action="{{ route('login') }}" method="post">
                                        @csrf

                                        <div class="form-outline mb-3">
                                            <label class="form-label">Correo</label>
                                            <input type="email" name="email" class="form-control form-control-sm"
                                                placeholder="Ingresar correo" />
                                        </div>

                                        <div class="form-outline mb-3">
                                            <label class="form-label">Contraseña</label>
                                            <input type="password" name="password" class="form-control form-control-sm"
                                                placeholder="Ingresar contraseña" />
                                        </div>

                                        <div class="text-center">
                                            <button class="btn btn-solid btn-block w-100" type="submit">Login</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-6 d-flex align-items-center gradient-custom-2">
                                <div class="text-white text-center px-2">
                                    <h4 class="mb-2">¡BIENVENIDO!</h4>
                                    <p class="small mb-0">Palabras bonitas pal admin</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous">
    </script>
</body>

</html>
