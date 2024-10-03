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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#reset-password-form').on('submit', function(event) {
        event.preventDefault(); // Evitar el envío normal del formulario
        
        // Recoger datos del formulario
        var formData = $(this).serialize(); // Serializa los datos del formulario

        $.ajax({
            url: 'https://fya-api.com:8443/api/password-reset', // Cambia esta URL si es necesario
            type: 'POST',
            data: formData,
            success: function(response) {
                // Manejar la respuesta exitosa
                alert(response.message || 'Contraseña restablecida exitosamente.');
                // Redirigir o realizar otra acción según sea necesario
            },
            error: function(xhr) {
                console.log(xhr);
                // Manejar errores
                if (xhr.status === 419) {
                    alert('El token ha expirado. Por favor, intenta de nuevo.');
                } else {
                    alert('Ocurrió un error. Intenta nuevamente.');
                }
            }
        });
    });
});
</script>
</html>