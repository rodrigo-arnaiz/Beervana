<!DOCTYPE html>
<html lang="es">

<head>
    @include('partials.admin-head')

</head>

<body>
    <div class="d-flex flex-column flex-lg-row">
        @include('partials.sidebar')
        <div class="flex-grow-1">
            @include('partials.nav')
            <div class="container-fluid mt-4 px-3">
                @yield('content')
            </div>
        </div>
    </div>
    @include('partials.admin-scripts')
</body>

</html>