<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistema de Coleta') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,.04);
        }
        .navbar-brand {
            font-weight: 600;
        }
        .card {
            box-shadow: 0 0 15px rgba(0,0,0,.05);
            border: none;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0,0,0,.1);
            font-weight: 600;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .btn {
            font-weight: 500;
        }
        .table {
            margin-bottom: 0;
        }
        .alert {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-light mb-4">
        <div class="container">
           
                <div class="todo" style="text-align:center">
<img src="https://images.tcdn.com.br/files/740836/themes/330/img/settings/Concordia.svg?8856fcac88e74c384a29cda4b13b02c0" style="width:100px">
    </div>
            <div>
           
               Bem-vindo {{ Auth::user()->name }}
         </div>

                             <div style="text-align:center">   
                            </a style="text-align:center">
                           
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        Sair
                                    </a>
                          
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>

            </div>
            
        
    </nav>

    <main>
        @yield('content')
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Scripts Adicionais -->
    @stack('scripts')

    <script>
        // Foco automático em campos de input quando a página carrega
        document.addEventListener('DOMContentLoaded', function() {
            const firstInput = document.querySelector('input[autofocus]');
            if (firstInput) {
                firstInput.focus();
            }
        });

        // Setup do CSRF Token para requisições AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
</main>
</body>
</html>