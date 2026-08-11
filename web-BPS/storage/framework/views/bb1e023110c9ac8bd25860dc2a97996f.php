
<header class="bg-blue-900 text-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <a href="<?php echo e(route('home')); ?>" class="font-bold text-lg tracking-wide hover:text-blue-200 transition">
            Portal Publikasi BPS
        </a>

        
        <div class="flex items-center gap-3">
            <?php if(isset($backRoute)): ?>
                <a href="<?php echo e($backRoute); ?>" class="text-sm hover:bg-blue-700 px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <?php echo e($backLabel ?? 'Kembali ke Beranda'); ?>

                </a>
            <?php endif; ?>

            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="px-4 py-2 bg-blue-600 rounded-lg hover:bg-blue-700 text-sm font-semibold transition">
                    Dashboard Admin
                </a>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-2 border border-blue-400 rounded-lg hover:bg-blue-800 text-sm font-semibold transition">
                        Logout
                    </button>
                </form>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="px-4 py-2 border border-blue-400 rounded-lg hover:bg-blue-800 text-sm font-semibold transition">
                    Login Admin
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php /**PATH E:\Kerja\Web-BPS\web-BPS\resources\views/partials/navbar.blade.php ENDPATH**/ ?>