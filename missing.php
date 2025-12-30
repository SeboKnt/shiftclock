<?php
require __DIR__ . '/components/common.php';

// Default 0 until PostgreSQL is configured
$total_seconds = 0;
$total_hours = 0;

renderPage('Accumulated', function () use ($total_hours) {
?>
    <section class="grid grid-cols-1 gap-6">
        <div class="glass p-8">
            <div class="text-[56px] sm:text-[72px] lg:text-[96px] leading-none font-semibold tracking-tight">
                <?= number_format($total_hours, 1) ?>
            </div>
            <div class="metric-label mt-2 text-sm">h</div>
        </div>
    </section>
<?php
});
?>
