<?php
include __DIR__ . '/header.php';
?>

<div class="max-w-4xl mx-auto my-12 glass-panel p-8 md:p-12 rounded-3xl border border-white/60 shadow-xl bg-white/70 backdrop-blur-md">
    <div class="flex items-center gap-3 mb-4">
        <span class="text-4xl"><?php if ($cmsItem['type'] === 'page'): ?>📄<?php else: ?>📝<?php endif; ?></span>
        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-pink-100 text-pink-700">
            <?php if ($cmsItem['type'] === 'page'): ?>Custom Page<?php else: ?>Blog Post<?php endif; ?>
        </span>
    </div>
    
    <h1 class="text-3xl md:text-5xl font-black text-gray-900 serif-font mt-2"><?= htmlspecialchars($cmsItem['title']) ?></h1>
    
    <?php if ($cmsItem['type'] === 'post'): ?>
        <div class="flex items-center gap-2 text-xs text-gray-400 font-bold uppercase tracking-wider mt-4">
            <span>Published by Admin</span>
            <span>•</span>
            <span><?= date('F j, Y', strtotime($cmsItem['created_at'] ?? 'now')) ?></span>
        </div>
    <?php endif; ?>

    <div class="border-t border-gray-200/50 mt-6 pt-6 text-gray-700 text-sm md:text-base leading-relaxed whitespace-pre-line space-y-4 font-medium">
        <?= nl2br(htmlspecialchars($cmsItem['content'])) ?>
    </div>
    
    <div class="pt-8 border-t border-gray-100 mt-8">
        <a href="/" class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 hover:text-pink-600 transition">
            &larr; Back to Home
        </a>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
