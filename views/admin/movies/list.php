<?php
$pageTitle = 'Gestionar Películas';

if (!isAdmin()) {
    header('Location: ?route=home');
    exit;
}

$movies = $movieModel->getAll(100);

include __DIR__ . '/../../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <div class="flex justify-between items-center mb-8">
        <h1 class="font-orbitron text-3xl font-bold text-gold-gradient">
            <i class="fas fa-film mr-3"></i>Gestionar Películas
        </h1>
        <a href="?route=admin/movies/create" class="hud-button px-6 py-3 rounded-lg font-rajdhani">
            <i class="fas fa-plus mr-2"></i>Agregar Película
        </a>
    </div>
    
    <div class="hud-container rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-black-light">
                <tr>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Poster</th>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Título</th>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Año</th>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Tipo</th>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Precio</th>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gold/20">
                <?php foreach ($movies as $movie): ?>
                <tr class="hover:bg-gold/5">
                    <td class="px-6 py-4">
                        <img src="<?= $movie['poster'] ?: 'https://via.placeholder.com/50x75/1A1A1A/D4AF37?text=GS' ?>" 
                             class="w-12 h-16 object-cover rounded">
                    </td>
                    <td class="px-6 py-4 text-white font-rajdhani"><?= htmlspecialchars($movie['title']) ?></td>
                    <td class="px-6 py-4 text-gray-400"><?= $movie['release_year'] ?></td>
                    <td class="px-6 py-4">
                        <?php if ($movie['is_free']): ?>
                            <span class="px-2 py-1 bg-green-500/20 text-green-400 rounded text-sm">Gratis</span>
                        <?php else: ?>
                            <span class="px-2 py-1 bg-gold/20 text-gold rounded text-sm">Renta</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-gold">$<?= number_format($movie['rental_price'], 2) ?></td>
                    <td class="px-6 py-4">
                        <a href="?route=movie/<?= $movie['slug'] ?>" class="text-gold hover:text-gold-light mr-3">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="?route=admin/movies/edit/<?= $movie['id'] ?>" class="text-blue-400 hover:text-blue-300 mr-3">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
