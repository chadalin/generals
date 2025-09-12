<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Стратегическая игра для военных Генералы</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AlpineJS CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    @yield('content')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
    function recruitSoldiers() {
        const count = document.getElementById('soldiersCount').value;
        // AJAX логика найма солдат
        console.log('Recruiting', count, 'soldiers');
        $('#recruitModal').modal('hide');
    }
    
    function trainScientists() {
        const count = document.getElementById('scientistsCount').value;
        // AJAX логика обучения ученых
        console.log('Training', count, 'scientists');
        $('#scientistsModal').modal('hide');
    }
    
    function hireGeneral() {
        const name = document.getElementById('generalName').value;
        // AJAX логика найма генерала
        console.log('Hiring general:', name);
        $('#hireGeneralModal').modal('hide');
    }
    </script>
    
    @yield('scripts')
</body>
</html>