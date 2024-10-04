<!-- resources/views/auth/reset-password.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
</head>
<body>
    <h1>Restablecer Contraseña</h1>
    <form id="reset-password-form">
        <input type="hidden" name="_token" value="{{ csrf_token() }}"> <!-- Este es el token CSRF -->
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">
        
        <label for="password">Nueva Contraseña:</label>
        <input type="password" name="password" required="">
        
        <label for="password_confirmation">Confirmar Nueva Contraseña:</label>
        <input type="password" name="password_confirmation" required="">
        
        <button type="submit">Restablecer Contraseña</button>
    </form>
</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reset-password-form');

    form.addEventListener('submit', function(event) {
        event.preventDefault(); // Evitar el envío normal del formulario
        
        // Recoger datos del formulario
        const formData = new FormData(form); // Recoge los datos del formulario
        const data = new URLSearchParams(formData); // Serializa los datos

        fetch('https://fya-api.com:8443/api/password-reset', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded' // Como se usa en serialize()
            },
            body: data
        })
        .then(response => {
            if (!response.ok) {
                throw response;
            }
            return response.json();
        })
        .then(data => {
            alert(data.message || 'Contraseña restablecida exitosamente.');
            // Redirigir o realizar otra acción según sea necesario
        })
        .catch(error => {
            error.json().then(err => {
                if (error.status === 419) {
                    alert('El token ha expirado. Por favor, intenta de nuevo.');
                } else {
                    alert('Ocurrió un error. Intenta nuevamente.');
                }
                console.log(err);
            });
        });
    });
});
</script>
</html>