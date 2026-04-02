    </main>
    
    <footer class="hud-container mt-20 border-t border-gold/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-gold to-gold-dark flex items-center justify-center">
                            <i class="fas fa-film text-black"></i>
                        </div>
                        <span class="font-orbitron text-xl font-bold text-gold-gradient">GSFILMS</span>
                    </div>
                    <p class="text-gray-400 font-rajdhani">
                        Tu plataforma de streaming favorita con las mejores películas.
                    </p>
                </div>
                
                <div>
                    <h4 class="font-orbitron text-gold mb-4">Explorar</h4>
                    <ul class="space-y-2 font-rajdhani">
                        <li><a href="?route=movies" class="text-gray-400 hover:text-gold transition">Películas</a></li>
                        <li><a href="?route=genres" class="text-gray-400 hover:text-gold transition">Géneros</a></li>
                        <li><a href="?route=movies?filter=free" class="text-gray-400 hover:text-gold transition">Gratuitas</a></li>
                        <li><a href="?route=movies?filter=new" class="text-gray-400 hover:text-gold transition">Nuevas</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-orbitron text-gold mb-4">Cuenta</h4>
                    <ul class="space-y-2 font-rajdhani">
                        <?php if (isLoggedIn()): ?>
                            <li><a href="?route=profile" class="text-gray-400 hover:text-gold transition">Mi Perfil</a></li>
                            <li><a href="?route=rentals" class="text-gray-400 hover:text-gold transition">Mis Rentas</a></li>
                            <li><a href="?route=favorites" class="text-gray-400 hover:text-gold transition">Favoritos</a></li>
                        <?php else: ?>
                            <li><a href="?route=login" class="text-gray-400 hover:text-gold transition">Iniciar Sesión</a></li>
                            <li><a href="?route=register" class="text-gray-400 hover:text-gold transition">Registrarse</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-orbitron text-gold mb-4">Contacto</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gold/10 flex items-center justify-center hover:bg-gold/20 transition">
                            <i class="fab fa-facebook-f text-gold"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gold/10 flex items-center justify-center hover:bg-gold/20 transition">
                            <i class="fab fa-twitter text-gold"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gold/10 flex items-center justify-center hover:bg-gold/20 transition">
                            <i class="fab fa-instagram text-gold"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gold/10 flex items-center justify-center hover:bg-gold/20 transition">
                            <i class="fab fa-youtube text-gold"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <hr class="border-gold/20 my-8">
            
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 font-rajdhani text-sm">
                    © 2024 GSFilms. Todos los derechos reservados.
                </p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-500 hover:text-gold text-sm font-rajdhani">Términos</a>
                    <a href="#" class="text-gray-500 hover:text-gold text-sm font-rajdhani">Privacidad</a>
                    <a href="#" class="text-gray-500 hover:text-gold text-sm font-rajdhani">Ayuda</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
