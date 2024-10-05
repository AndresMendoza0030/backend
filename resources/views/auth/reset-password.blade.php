<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&display=swap">
    <style>
        body {
            font-family: 'Raleway', sans-serif;
            background: url('/images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            width: 100%;
            margin: 0;
        }
        
        .back-button {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: #D10A11;
            color: #FFFFFF;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            padding: 10px;
            cursor: pointer;
        }
        
        .back-button:hover {
            background-color: #a00;
        }
        
        .login-container {
            position: relative;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 40px 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            text-align: center;
            z-index: 10;
        }
        
        .login-container h1 {
            margin-bottom: 20px;
            color: #000000;
        }
        
        .login-container form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .login-container input {
            margin: 10px 0;
            padding: 10px;
            width: 100%;
            max-width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        
        .login-container button {
            margin: 10px 0;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #D10A11;
            color: #FFFFFF;
            font-weight: bold;
            cursor: pointer;
            max-width: 300px;
            width: 100%;
        }
        
        .login-container button:hover {
            background-color: #a00;
        }
        
        .success-message {
            color: green;
            margin-top: 10px;
        }
        
        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <button class="back-button" onclick="window.location.href='/login'">Volver</button>
    <div class="login-container">
        <h1>Restablecer Contraseña</h1>
        <form id="reset-password-form">
            <input type="hidden" name="_token" value="{{ csrf_token() }}"> <!-- Este es el token CSRF -->
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            
            <label for="password">Nueva Contraseña:</label>
            <input type="password" name="password" required="">
            
            <label for="password_confirmation">Confirmar Nueva Contraseña:</label>
            <input type="password" name="password_confirmation" required="">
            
            <button type="submit" class="submit-button">Restablecer Contraseña</button>
        </form>
    </div>
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
</body>
</html>