<?php
require __DIR__ . '/components/common.php';

// Placeholder data: connect to your DB later
$total_seconds = 3_456_789; // example
$total_hours = round($total_seconds / 3600, 1);

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
