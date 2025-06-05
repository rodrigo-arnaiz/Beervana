<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.admin-head')
    
</head>
<body>
    <div class="d-flex">
        @include('partials.sidebar')
         <div class="flex-grow-1">
            @include('partials.nav')
            <div class="container">
                @yield('content')
            </div>
        </div>
    </div>
    @include('partials.admin-scripts')
</body>
</html>
