<?php
$pageTitle = 'Login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor completa todos los campos',
                background: '#1A1A1A',
                color: '#FFFFFF'
            });
        </script>";
    } else {
        $user = $userModel->verifyPassword($email, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: '¡Bienvenido!',
                    text: 'Has iniciado sesión correctamente',
                    background: '#1A1A1A',
                    color: '#FFFFFF',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = '?route=home';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Email o contraseña incorrectos',
                    background: '#1A1A1A',
                    color: '#FFFFFF'
                });
            </script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | GSFilms</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: { DEFAULT: '#D4AF37', light: '#FFD700', dark: '#B8860B' },
                        black: { DEFAULT: '#0A0A0A', light: '#1A1A1A' }
                    },
                    fontFamily: {
                        orbitron: ['Orbitron', 'sans-serif'],
                        rajdhani: ['Rajdhani', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #0A0A0A 0%, #1A1A1A 50%, #0A0A0A 100%);
            font-family: 'Rajdhani', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-20">
    <div style="background: rgba(26, 26, 26, 0.95); border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 1rem; padding: 2rem; width: 100%; max-width: 28rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 5rem; height: 5rem; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, #D4AF37, #B8860B); display: flex; align-items: center; justify-content: center; box-shadow: 0 0 20px rgba(212, 175, 55, 0.5);">
                <i class="fas fa-user text-black text-2xl"></i>
            </div>
            <h2 style="font-family: 'Orbitron', sans-serif; font-size: 1.5rem; font-weight: bold; background: linear-gradient(135deg, #FFD700, #D4AF37); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">INICIAR SESIÓN</h2>
            <p style="color: #9ca3af; margin-top: 0.5rem;">Accede a tu cuenta de GSFilms</p>
        </div>
        
        <form action="?route=login" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div>
                <label style="display: block; color: #D4AF37; margin-bottom: 0.5rem;">Email</label>
                <input type="email" name="email" required 
                       style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; background: rgba(10, 10, 10, 0.8); border: 1px solid rgba(212, 175, 55, 0.3); color: white;"
                       placeholder="tu@email.com">
            </div>
            
            <div>
                <label style="display: block; color: #D4AF37; margin-bottom: 0.5rem;">Contraseña</label>
                <input type="password" name="password" required 
                       style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; background: rgba(10, 10, 10, 0.8); border: 1px solid rgba(212, 175, 55, 0.3); color: white;"
                       placeholder="••••••••">
            </div>
            
            <button type="submit" style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; background: linear-gradient(135deg, #D4AF37, #B8860B); border: 2px solid #FFD700; color: black; font-weight: bold; cursor: pointer; font-family: 'Rajdhani', sans-serif;">
                <i class="fas fa-sign-in-alt mr-2"></i>Entrar
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 1.5rem;">
            <p style="color: #9ca3af;">
                ¿No tienes cuenta? 
                <a href="?route=register" style="color: #D4AF37; font-weight: 600;">
                    Regístrate aquí
                </a>
            </p>
        </div>
    </div>
</body>
</html>
