<?php
require __DIR__ . '/components/common.php';

// Default null until PostgreSQL is configured
$today = new DateTimeImmutable('today');
$daily_hours = null;

// Today's hours
$todayKey = $today->format('Y-m-d');
$todayHours = (float)($daily_hours[$todayKey] ?? 0);

// This week (Mon-Sun)
$weekStart = $today->modify('- ' . ((int)$today->format('N') - 1) . ' days');
$weekHours = 0.0;
$weekDays = [];
for ($i = 0; $i < 7; $i++) {
    $date = $weekStart->modify("+{$i} days");
    $key = $date->format('Y-m-d');
    $h = (float)($daily_hours[$key] ?? 0);
    $weekHours += $h;
    $weekDays[] = ['date' => $key, 'h' => $h, 'dow' => $date->format('D')];
}

// Streak: consecutive days with >0 hours
$streak = 0;
for ($i = 0; $i < 60; $i++) {
    $key = $today->modify('-' . $i . ' days')->format('Y-m-d');
    if (($daily_hours[$key] ?? 0) > 0) {
        $streak++;
    } else {
        break;
    }
}

// Last 30 days for trend sparkline
$last30 = [];
$sum30 = 0.0;
for ($i = 29; $i >= 0; $i--) {
    $date = $today->modify('-' . $i . ' days');
    $key = $date->format('Y-m-d');
    $h = (float)($daily_hours[$key] ?? 0);
    $last30[] = ['date' => $key, 'h' => $h];
    $sum30 += $h;
}
$avg30 = $sum30 / 30.0;

renderPage('Overview', function () use ($todayHours, $weekHours, $streak, $last30, $avg30, $weekDays) {
?>
    <!-- Key Metrics -->
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="glass p-6">
            <div class="flex items-baseline gap-2">
                <div class="text-[40px] font-semibold">
                    <?= number_format($todayHours, 1) ?>
                </div>
                <div class="metric-label text-xs">h</div>
            </div>
            <div class="text-[10px] metric-label mt-1">Today</div>
        </div>

        <div class="glass p-6">
            <div class="flex items-baseline gap-2">
                <div class="text-[40px] font-semibold">
                    <?= number_format($weekHours, 1) ?>
                </div>
                <div class="metric-label text-xs">h</div>
            </div>
            <div class="text-[10px] metric-label mt-1">This Week</div>
        </div>

        <div class="glass p-6">
            <div class="flex items-baseline gap-2">
                <div class="text-[40px] font-semibold">
                    <?= $streak ?>
                </div>
                <div class="metric-label text-xs">d</div>
            </div>
            <div class="text-[10px] metric-label mt-1">Streak</div>
        </div>
    </section>

    <!-- 30-Day Trend -->
    <section class="grid grid-cols-1 gap-4 mb-6">
        <div class="glass p-6">
            <div class="flex items-baseline justify-between mb-4">
                <div class="text-xs font-semibold text-slate-300">30-Day Trend</div>
                <div class="flex items-baseline gap-1">
                    <div class="text-[13px] text-slate-400"><?= number_format($avg30, 1) ?></div>
                    <div class="text-[9px] metric-label">h avg</div>
                </div>
            </div>
            <div class="flex items-end gap-[2px]">
                <?php 
                $max = max(array_column($last30, 'h'));
                $max = $max > 0 ? $max : 1;
                foreach ($last30 as $day): 
                    $pct = ($day['h'] / $max) * 100;
                    $isToday = ($day['date'] === date('Y-m-d'));
                ?>
                    <div class="flex-1 bg-white/5 rounded-sm overflow-hidden" style="height: 60px;" title="<?= $day['date'] ?>: <?= number_format($day['h'], 1) ?>h">
                        <div class="w-full bg-gradient-to-t from-blue-500/70 to-blue-400/70 rounded-sm <?= $isToday ? 'ring-1 ring-blue-400' : '' ?>" 
                             style="height: <?= number_format($pct, 2) ?>%; margin-top: auto;"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Weekly Breakdown -->
    <section class="grid grid-cols-1 gap-4">
        <div class="glass p-6">
            <div class="text-xs font-semibold text-slate-300 mb-4">This Week (Mon-Sun)</div>
            <div class="space-y-2">
                <?php foreach ($weekDays as $day): 
                    $pct = min(100, ($day['h'] / 24) * 100);
                    $isToday = ($day['date'] === date('Y-m-d'));
                ?>
                    <div class="flex items-center gap-3">
                        <div class="text-[11px] metric-label w-8 <?= $isToday ? 'text-blue-400 font-bold' : '' ?>">
                            <?= $day['dow'] ?>
                        </div>
                        <div class="flex-1 relative h-6 bg-white/5 rounded overflow-hidden">
                            <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-blue-500/60 to-cyan-400/60 rounded" 
                                 style="width: <?= number_format($pct, 2) ?>%"></div>
                        </div>
                        <div class="text-[12px] text-slate-300 w-12 text-right">
                            <?= number_format($day['h'], 1) ?>h
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

<?php
});
?>
