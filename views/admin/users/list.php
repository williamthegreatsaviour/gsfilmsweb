<?php
$pageTitle = 'Gestionar Usuarios';

if (!isAdmin()) {
    header('Location: ?route=home');
    exit;
}

$users = $userModel->getAll();

include __DIR__ . '/../../layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
    <h1 class="font-orbitron text-3xl font-bold text-gold-gradient mb-8">
        <i class="fas fa-users mr-3"></i>Gestionar Usuarios
    </h1>
    
    <div class="hud-container rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-black-light">
                <tr>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">ID</th>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Nombre</th>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Email</th>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Rol</th>
                    <th class="px-6 py-3 text-left text-gold font-rajdhani">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gold/20">
                <?php foreach ($users as $user): ?>
                <tr class="hover:bg-gold/5">
                    <td class="px-6 py-4 text-gray-400">#<?= $user['id'] ?></td>
                    <td class="px-6 py-4 text-white font-rajdhani"><?= htmlspecialchars($user['name']) ?></td>
                    <td class="px-6 py-4 text-gray-400"><?= htmlspecialchars($user['email']) ?></td>
                    <td class="px-6 py-4">
                        <?php 
                        $roleColors = [
                            'super_admin' => 'bg-red-500/20 text-red-400',
                            'moderator' => 'bg-blue-500/20 text-blue-400',
                            'client' => 'bg-green-500/20 text-green-400'
                        ];
                        $color = $roleColors[$user['role']] ?? 'bg-gray-500/20 text-gray-400';
                        ?>
                        <span class="px-2 py-1 rounded text-sm <?= $color ?>">
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-400"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
