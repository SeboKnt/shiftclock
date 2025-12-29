<?php
require __DIR__ . '/components/common.php';

// Build a year view: every day with vertical water glass and overtime badge
$today = new DateTimeImmutable('today');
$yearStart = new DateTimeImmutable($today->format('Y-01-01'));
$yearEnd = new DateTimeImmutable($today->format('Y-12-31'));

// External daily hours map (date => float) or placeholder
if (!isset($daily_hours_year) || !is_array($daily_hours_year)) {
    $daily_hours_year = [];
    $cursor = $yearStart;
    while ($cursor <= $yearEnd) {
        $key = $cursor->format('Y-m-d');
        $daily_hours_year[$key] = round((random_int(0, 10) + random_int(0, 6)), 1); // approx 0..16h
        $cursor = $cursor->modify('+1 day');
    }
}

// Build months with detailed stats
$months = [];
$yearTotal = 0.0;
$cursor = $yearStart;

while ($cursor <= $yearEnd) {
    $monthName = $cursor->format('M');
    $monthStart = new DateTimeImmutable($cursor->format('Y-m-01'));
    $monthEnd = new DateTimeImmutable($cursor->format('Y-m-t'));
    
    // Start from Monday of the week containing the 1st
    $firstDayOfWeek = (int)$monthStart->format('N'); // 1=Mon, 7=Sun
    $weekStart = $monthStart->modify('-' . ($firstDayOfWeek - 1) . ' days');
    
    $weeks = [];
    $monthTotal = 0.0;
    $monthDays = 0;
    $weekCursor = $weekStart;
    
    // Build weeks until we cover the entire month
    while ($weekCursor <= $monthEnd) {
        $week = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $weekCursor->modify("+{$i} days");
            $key = $date->format('Y-m-d');
            $h = (float)($daily_hours_year[$key] ?? 0);
            $over = max(0.0, $h - 8.0);
            $inMonth = ($date >= $monthStart && $date <= $monthEnd);
            $isWeekend = ((int)$date->format('N') >= 6);
            
            if ($inMonth) {
                $monthTotal += $h;
                $monthDays++;
            }
            
            $week[] = [
                'date' => $key,
                'h' => $h,
                'over' => $over,
                'inMonth' => $inMonth,
                'isToday' => ($key === $today->format('Y-m-d')),
                'isWeekend' => $isWeekend
            ];
        }
        $weeks[] = $week;
        $weekCursor = $weekCursor->modify('+7 days');
        
        if ($weekCursor > $monthEnd) break;
    }
    
    $yearTotal += $monthTotal;
    $monthAvg = $monthDays > 0 ? $monthTotal / $monthDays : 0;
    
    $months[] = [
        'name' => $monthName,
        'weeks' => $weeks,
        'total' => $monthTotal,
        'avg' => $monthAvg
    ];
    
    $cursor = $cursor->modify('first day of next month');
}

renderPage('Calendar', function () use ($months, $yearTotal, $today) {
?>
    <!-- Monthly Calendars -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php foreach ($months as $month): ?>
            <div class="glass p-4 hover:border-slate-600/50 transition-all">
                <!-- Month Header -->
                <div class="mb-3">
                    <div class="text-sm font-semibold text-slate-200"><?= $month['name'] ?></div>
                </div>

                <!-- Weekday Headers -->
                <div class="grid grid-cols-7 gap-[3px] mb-2">
                    <?php foreach (['M','T','W','T','F','S','S'] as $idx => $day): ?>
                        <div class="text-[9px] text-center metric-label <?= $idx >= 5 ? 'text-blue-400/50' : '' ?>"><?= $day ?></div>
                    <?php endforeach; ?>
                </div>

                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 gap-[3px] mb-3">
                    <?php foreach ($month['weeks'] as $week): ?>
                        <?php foreach ($week as $cell): ?>
                            <?php
                                $basePct = min(100, max(0, ($cell['h'] / 24) * 100));
                                $thresholdPct = (8 / 24) * 100;
                                $overHours = $cell['over'];
                                $isToday = $cell['isToday'];
                                $isWeekend = $cell['isWeekend'];
                            ?>
                            <a href="workweek.php?date=<?= htmlspecialchars($cell['date']) ?>" 
                               class="relative flex flex-col items-center gap-[2px] group <?= !$cell['inMonth'] ? 'opacity-20 pointer-events-none' : '' ?>"
                               title="<?= htmlspecialchars($cell['date']) ?>: <?= number_format($cell['h'], 1) ?>h">
                                <div class="water-xs 
                                    <?= ($overHours > 0) ? 'over-glow' : '' ?> 
                                    <?= $isToday ? 'ring-2 ring-blue-400 ring-offset-1 ring-offset-slate-900/50' : '' ?> 
                                    <?= $isWeekend && $cell['inMonth'] ? 'ring-1 ring-blue-500/20' : '' ?> 
                                    group-hover:scale-110 group-hover:brightness-110 transition-all duration-200">
                                    <div class="water-fill" style="height: <?= number_format($basePct, 2) ?>%"></div>
                                    <div class="threshold-line" style="bottom: <?= number_format($thresholdPct, 2) ?>%"></div>
                                </div>
                                <div class="day-num <?= $isToday ? 'text-blue-400 font-bold' : '' ?> <?= !$cell['inMonth'] ? 'opacity-0' : '' ?>">
                                    <?= date('j', strtotime($cell['date'])) ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
<?php
});
?>
