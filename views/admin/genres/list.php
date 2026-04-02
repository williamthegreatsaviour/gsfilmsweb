<?php
$pageTitle = 'Gestionar Géneros';

if (!isAdmin()) {
    header('Location: ?route=home');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $color = $_POST['color'] ?? '#D4AF37';
    $description = $_POST['description'] ?? '';
    
    if (empty($name)) {
        $error = 'El nombre es obligatorio';
    } else {
        $genreId = $genreModel->create([
            'name' => $name,
            'slug' => $slug,
            'color' => $color,
            'description' => $description
        ]);
        $success = 'Género creado correctamente';
    }
}

$genres = $genreModel->getAll();

include __DIR__ . '/../../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-8">
        <i class="fas fa-tags mr-3"></i>Gestionar Géneros
    </h1>
    
    <?php if ($error): ?>
    <div class="bg-red-500/20 border border-red-500 text-red-400 px-4 py-3 rounded-lg mb-6">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="bg-green-500/20 border border-green-500 text-green-400 px-4 py-3 rounded-lg mb-6">
        <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>
    
    <div class="hud-container rounded-xl p-6 mb-8">
        <h2 class="font-orbitron text-xl text-gold mb-4">Crear Nuevo Género</h2>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-gold font-rajdhani mb-2">Nombre</label>
                <input type="text" name="name" required 
                       class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                       placeholder="Acción">
            </div>
            <div>
                <label class="block text-gold font-rajdhani mb-2">Color</label>
                <input type="color" name="color" value="#D4AF37" 
                       class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani h-12">
            </div>
            <div>
                <label class="block text-gold font-rajdhani mb-2">Descripción</label>
                <input type="text" name="description" 
                       class="hud-input w-full px-4 py-3 rounded-lg font-rajdhani"
                       placeholder="Descripción del género">
            </div>
            <div class="flex items-end">
                <button type="submit" class="hud-button w-full py-3 rounded-lg font-rajdhani font-bold">
                    <i class="fas fa-plus mr-2"></i>Crear
                </button>
            </div>
        </form>
    </div>
    
    <div class="hud-container rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-black-light">
                <tr>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">ID</th>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Nombre</th>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Slug</th>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Color</th>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Películas</th>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gold/20">
                <?php foreach ($genres as $genre): ?>
                <tr class="hover:bg-gold/5">
                    <td class="px-6 py-4 text-gray-400">#<?= $genre['id'] ?></td>
                    <td class="px-6 py-4 text-white font-rajdhani font-bold"><?= htmlspecialchars($genre['name']) ?></td>
                    <td class="px-6 py-4 text-gray-400"><?= htmlspecialchars($genre['slug']) ?></td>
                    <td class="px-6 py-4">
                        <span class="w-6 h-6 rounded inline-block" style="background: <?= $genre['color'] ?>"></span>
                    </td>
                    <td class="px-6 py-4 text-gray-400"><?= $genreModel->getMovieCount($genre['id']) ?></td>
                    <td class="px-6 py-4">
                        <a href="?route=genre/<?= $genre['slug'] ?>" class="text-gold hover:text-gold-light mr-3">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="?route=admin/genres/delete/<?= $genre['id'] ?>" 
                           class="text-red-400 hover:text-red-300"
                           onclick="return confirm('¿Eliminar este género?');">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
