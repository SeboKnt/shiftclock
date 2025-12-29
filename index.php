<?php
require __DIR__ . '/components/common.php';

// Placeholder recent daily hours map (date => float)
$today = new DateTimeImmutable('today');
$daily_hours = [];
for ($i = 0; $i < 60; $i++) {
    $date = $today->modify('-' . $i . ' days');
    $daily_hours[$date->format('Y-m-d')] = round((random_int(0, 10) + random_int(0, 6)), 1); // 0..16h approx
}

// Metrics
$monthStart = $today->modify('first day of this month');
$month_hours = 0.0;
$cursor = $monthStart;
while ($cursor <= $today) {
    $key = $cursor->format('Y-m-d');
    $month_hours += (float)($daily_hours[$key] ?? 0);
    $cursor = $cursor->modify('+1 day');
}

$sum7 = 0.0;
$sum30 = 0.0;
for ($i = 0; $i < 30; $i++) {
    $key = $today->modify('-' . $i . ' days')->format('Y-m-d');
    $val = (float)($daily_hours[$key] ?? 0);
    if ($i < 7) { $sum7 += $val; }
    $sum30 += $val;
}
$avg30 = $sum30 / 30.0;

renderPage('Overview', function () use ($month_hours, $sum7, $sum30, $avg30) {
?>
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Month total -->
        <div class="glass p-6 sm:p-8">
            <div class="flex items-baseline gap-3">
                <div class="text-[40px] sm:text-[56px] font-semibold">
                    <?= number_format($month_hours, 1) ?>
                </div>
                <div class="metric-label text-xs">h</div>
            </div>
        </div>

        <!-- Compact metrics: 7d / 30d / Avg -->
        <div class="glass p-6 sm:p-8">
            <div class="grid grid-cols-3 gap-4">
                <div class="flex items-baseline gap-2">
                    <div class="text-[24px] sm:text-[28px] font-semibold">
                        <?= number_format($sum7, 1) ?>
                    </div>
                    <div class="metric-label text-xs">7d</div>
                </div>
                <div class="flex items-baseline gap-2">
                    <div class="text-[24px] sm:text-[28px] font-semibold">
                        <?= number_format($sum30, 1) ?>
                    </div>
                    <div class="metric-label text-xs">30d</div>
                </div>
                <div class="flex items-baseline gap-2">
                    <div class="text-[24px] sm:text-[28px] font-semibold">
                        <?= number_format($avg30, 1) ?>
                    </div>
                    <div class="metric-label text-xs">Avg</div>
                </div>
            </div>
        </div>
    </section>


<?php
});
?>
