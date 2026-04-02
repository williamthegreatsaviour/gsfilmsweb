<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'GSFilms' ?> | GSFilms</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: {
                            DEFAULT: '#D4AF37',
                            light: '#FFD700',
                            dark: '#B8860B',
                            glow: 'rgba(212, 175, 55, 0.5)'
                        },
                        black: {
                            DEFAULT: '#0A0A0A',
                            light: '#1A1A1A',
                            lighter: '#252525'
                        }
                    },
                    fontFamily: {
                        orbitron: ['Orbitron', 'sans-serif'],
                        rajdhani: ['Rajdhani', 'sans-serif']
                    },
                    boxShadow: {
                        'glow': '0 0 20px rgba(212, 175, 55, 0.5)',
                        'glow-sm': '0 0 10px rgba(212, 175, 55, 0.3)',
                        'glow-lg': '0 0 40px rgba(212, 175, 55, 0.6)'
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --gold: #D4AF37;
            --gold-light: #FFD700;
            --black: #0A0A0A;
            --black-light: #1A1A1A;
            --white: #FFFFFF;
        }
        
        body {
            background: linear-gradient(135deg, #0A0A0A 0%, #1A1A1A 50%, #0A0A0A 100%);
            font-family: 'Rajdhani', sans-serif;
        }
        
        .hud-container {
            background: rgba(26, 26, 26, 0.95);
            border: 1px solid rgba(212, 175, 55, 0.3);
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.2), inset 0 0 30px rgba(0, 0, 0, 0.5);
        }
        
        .hud-glow {
            position: relative;
        }
        
        .hud-glow::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, transparent, rgba(212, 175, 55, 0.3), transparent);
            border-radius: inherit;
            z-index: -1;
            animation: hud-pulse 2s ease-in-out infinite;
        }
        
        @keyframes hud-pulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }
        
        .hud-button {
            background: linear-gradient(135deg, #D4AF37 0%, #B8860B 100%);
            border: 2px solid #FFD700;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .hud-button:hover {
            box-shadow: 0 0 30px rgba(212, 175, 55, 0.6);
            transform: translateY(-2px);
        }
        
        .hud-input {
            background: rgba(10, 10, 10, 0.8);
            border: 1px solid rgba(212, 175, 55, 0.3);
            color: #FFFFFF;
            transition: all 0.3s ease;
        }
        
        .hud-input:focus {
            border-color: #D4AF37;
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.3);
            outline: none;
            pointer-events: auto;
        }
        
        input, button, select, textarea {
            pointer-events: auto !important;
        }
        
        .movie-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform-style: preserve-3d;
        }
        
        .movie-card:hover {
            transform: scale(1.05) translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5), 0 0 30px rgba(212, 175, 55, 0.3);
        }
        
        .movie-card .poster-overlay {
            background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, transparent 100%);
        }
        
        .nav-link {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #D4AF37, #FFD700);
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        
        .genre-chip {
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.3);
            transition: all 0.3s ease;
        }
        
        .genre-chip:hover {
            background: rgba(212, 175, 55, 0.3);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.3);
        }
        
        .scan-line {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 999;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(0, 0, 0, 0.1) 2px,
                rgba(0, 0, 0, 0.1) 4px
            );
            opacity: 0.3;
        }
        
        .text-gold-gradient {
            background: linear-gradient(135deg, #FFD700 0%, #D4AF37 50%, #B8860B 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .gradient-border {
            position: relative;
        }
        
        .gradient-border::before {
            content: '';
            position: absolute;
            inset: 0;
            padding: 2px;
            background: linear-gradient(135deg, #D4AF37, #FFD700, #D4AF37);
            border-radius: inherit;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }
        
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #0A0A0A;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #D4AF37, #B8860B);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #FFD700;
        }
    </style>
</head>
<body class="min-h-screen text-white">
    <!-- <div class="scan-line"></div> -->
    
    <nav class="hud-container fixed top-0 left-0 right-0 z-50 border-b border-gold/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center space-x-4">
                    <a href="?route=home" class="flex items-center space-x-2">
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-gold to-gold-dark flex items-center justify-center shadow-glow">
                            <i class="fas fa-film text-black text-xl"></i>
                        </div>
                        <span class="font-orbitron text-2xl font-bold text-gold-gradient">GSFILMS</span>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="?route=home" class="nav-link font-rajdhani text-lg <?= $page === 'home' ? 'active text-gold' : 'text-white' ?>">
                        <i class="fas fa-home mr-2"></i>Inicio
                    </a>
                    <a href="?route=movies" class="nav-link font-rajdhani text-lg <?= $page === 'movies' ? 'active text-gold' : 'text-white' ?>">
                        <i class="fas fa-play-circle mr-2"></i>Películas
                    </a>
                    <a href="?route=genres" class="nav-link font-rajdhani text-lg <?= $page === 'genres' ? 'active text-gold' : 'text-white' ?>">
                        <i class="fas fa-tags mr-2"></i>Géneros
                    </a>
                    <?php if (isLoggedIn()): ?>
                        <a href="?route=favorites" class="nav-link font-rajdhani text-lg <?= $page === 'favorites' ? 'active text-gold' : 'text-white' ?>">
                            <i class="fas fa-heart mr-2"></i>Favoritos
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="flex items-center space-x-4">
                    <form action="?route=search" method="GET" class="hidden lg:block">
                        <input type="hidden" name="route" value="search">
                        <div class="relative">
                            <input type="text" name="q" placeholder="Buscar..." 
                                   class="hud-input rounded-full px-5 py-2 pl-12 w-64 font-rajdhani">
                            <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gold/50"></i>
                        </div>
                    </form>
                    
                    <?php if (isLoggedIn()): ?>
                        <div class="relative group">
                            <button class="flex items-center space-x-2 hud-button px-4 py-2 rounded-full">
                                <i class="fas fa-user"></i>
                                <span class="font-rajdhani"><?= htmlspecialchars($currentUser['name']) ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 hud-container rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300">
                                <a href="?route=profile" class="block px-4 py-3 hover:bg-gold/10">
                                    <i class="fas fa-user-circle mr-2 text-gold"></i>Mi Perfil
                                </a>
                                <a href="?route=rentals" class="block px-4 py-3 hover:bg-gold/10">
                                    <i class="fas fa-clock mr-2 text-gold"></i>Mis Rentas
                                </a>
                                <?php if (isAdmin()): ?>
                                    <hr class="border-gold/30 my-2">
                                    <a href="?route=admin/dashboard" class="block px-4 py-3 hover:bg-gold/10">
                                        <i class="fas fa-cog mr-2 text-gold"></i>Admin
                                    </a>
                                <?php endif; ?>
                                <hr class="border-gold/30 my-2">
                                <a href="?route=logout" class="block px-4 py-3 hover:bg-red-500/20 text-red-400">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Salir
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="?route=login" class="hud-button px-6 py-2 rounded-full font-rajdhani font-semibold">
                            <i class="fas fa-sign-in-alt mr-2"></i>Entrar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <main class="pt-20 min-h-screen">
