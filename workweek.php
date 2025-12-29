<?php
require __DIR__ . '/components/common.php';

// Accept ?date to focus a specific week; default today
$focusDate = isset($_GET['date']) ? new DateTimeImmutable($_GET['date']) : new DateTimeImmutable('today');
// Compute Monday as start of week for EU-style weeks
$dow = (int)$focusDate->format('N'); // 1..7 (Mon..Sun)
$weekStart = $focusDate->modify('-' . ($dow - 1) . ' days');

// Placeholder daily hours for the week (Mo..So) – replace with DB values
$daily = [];
$weekly_hours = 0;
for ($i = 0; $i < 7; $i++) {
    $day = $weekStart->modify("+{$i} days");
    $h = [5,6,7,8,9,10,11][array_rand([5,6,7,8,9,10,11])];
    $daily[$day->format('Y-m-d')] = $h;
    $weekly_hours += $h;
}
// Progress against 40h and overtime
$benchmark = 40;
$completed = min($benchmark, $weekly_hours);
$overtime = max(0, $weekly_hours - $benchmark);
$pct = $benchmark > 0 ? min(100, max(0, ($completed / $benchmark) * 100)) : 0;

renderPage('Workweek', function () use ($weekly_hours, $pct, $daily, $weekStart, $completed, $overtime) {
?>
    <section class="grid grid-cols-1 gap-6">
        <div class="glass p-6 sm:p-8">
            <div class="flex items-center gap-6 mb-6">
                <div class="flex items-baseline gap-2">
                    <div class="text-[32px] sm:text-[40px] font-semibold">
                        <?= number_format($completed) ?>
                    </div>
                    <div class="metric-label text-xs">h</div>
                </div>
                <div class="flex items-baseline gap-2">
                    <div class="text-[20px] sm:text-[24px] font-semibold text-blue-300">
                        +<?= number_format($overtime) ?>
                    </div>
                    <div class="metric-label text-xs">h</div>
                </div>
            </div>

            <div>
                <div class="relative h-3 w-full rounded-full bg-white/10 overflow-hidden">
                    <div class="absolute inset-y-0 left-0 rounded-full bg-gradient-to-r from-blue-400 via-cyan-400 to-teal-400" style="width: <?= number_format($pct, 2) ?>%"></div>
                    <?php $benchPct = 100; ?>
                    <div class="absolute inset-y-0" style="left: <?= number_format($benchPct,2) ?>%">
                        <div class="w-[2px] h-full bg-white/30"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass p-6 sm:p-8">
            <div class="grid grid-cols-7 gap-3">
                <?php
                $labels = ['M','T','W','T','F','S','S'];
                for ($i = 0; $i < 7; $i++):
                    $date = $weekStart->modify("+{$i} days");
                    $key = $date->format('Y-m-d');
                    $h = $daily[$key] ?? 0;
                    $pct = min(100, max(0, ($h / 24) * 100));
                    $thresholdPct = (8 / 24) * 100;
                ?>
                    <div class="flex flex-col items-center">
                        <div class="text-[10px] metric-label mb-2"><?= $labels[$i] ?></div>
                        <div class="w-full">
                            <div class="water-h">
                                <div class="water-fill-h" style="width: <?= number_format($pct, 2) ?>%"></div>
                                <div class="threshold-line-h" style="left: <?= number_format($thresholdPct, 2) ?>%"></div>
                            </div>
                        </div>
                        <div class="text-[11px] text-slate-300 mt-2"><?= number_format($h, 0) ?>h</div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>
<?php
});
?>
