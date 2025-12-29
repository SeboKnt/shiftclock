<?php
function renderPage(string $title, callable $mainContent): void {
    $current = basename($_SERVER['PHP_SELF'] ?? 'index.php');
    $now = new DateTimeImmutable('now');
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title><?= htmlspecialchars($title) ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            :root {
                --bg-start: #1e293b;
                --bg-end: #0f172a;
                --card-bg: rgba(255, 255, 255, 0.03);
                --card-border: rgba(255, 255, 255, 0.07);
                --text-soft: rgba(229, 231, 235, 0.75);
            }
            html, body { height: 100%; }
            body {
                font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
                color: #e5e7eb;
                background:
                    radial-gradient(1200px 1200px at 20% 0%, var(--bg-start) 0%, var(--bg-end) 60%),
                    linear-gradient(180deg, #0b1220, #0b1220);
            }
            .glass {
                background: var(--card-bg);
                border: 1px solid var(--card-border);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border-radius: 14px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.30), inset 0 1px 0 rgba(255,255,255,0.04);
                transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
            }
            .glass:hover { border-color: rgba(255,255,255,0.12); box-shadow: 0 10px 28px rgba(0,0,0,0.34), inset 0 1px 0 rgba(255,255,255,0.06); }
            .nav-glass {
                background: rgba(2, 6, 23, 0.6); /* slate-950 with opacity */
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border-bottom: 1px solid var(--card-border);
            }
            .metric-label { color: var(--text-soft); }
            .blue-0 { background-color: rgba(30, 58, 138, 0.18); }
            .blue-1 { background-color: rgba(37, 99, 235, 0.40); }
            .blue-2 { background-color: rgba(59, 130, 246, 0.60); }
            .blue-3 { background-color: rgba(96, 165, 250, 0.78); }
            .blue-4 { background-color: rgba(147, 197, 253, 0.96); }
            .grid-cell {
                width: 14px; height: 14px; border-radius: 3px;
                border: 1px solid rgba(255,255,255,0.10);
                transition: transform .12s ease, box-shadow .12s ease;
            }
            .grid-cell:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 10px rgba(0,0,0,0.25);
            }
            @media (min-width: 640px) {
                .grid-cell { width: 16px; height: 16px; }
            }
            /* Water level components for daily hours */
            .water { position: relative; width: 100%; height: 40px; border: 1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.05); border-radius: 6px; overflow: hidden; }
            .sm-water { height: 28px; border-radius: 5px; }
            .water-fill { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(37,99,235,0.60), rgba(147,197,253,0.70)); transition: height .25s ease; }
            .water-label { position: absolute; top: 4px; right: 6px; font-size: 11px; color: #e5e7eb; }
            /* Horizontal water bar */
            .water-h { position: relative; width: 100%; height: 12px; border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.06); border-radius: 6px; overflow: hidden; box-shadow: inset 0 1px 0 rgba(255,255,255,0.06), 0 2px 8px rgba(0,0,0,0.25); }
            .water-fill-h { position: absolute; top: 0; left: 0; height: 100%; background: linear-gradient(90deg, rgba(37,99,235,0.60), rgba(147,197,253,0.70)); transition: width .25s ease; box-shadow: inset 0 1px 0 rgba(255,255,255,0.15); }
            .water-fill-h::after { content: ""; position: absolute; top: 0; bottom: 0; right: 0; width: 2px; background: linear-gradient(0deg, rgba(255,255,255,0.6), rgba(255,255,255,0)); }
            .threshold-line-h { position: absolute; top: 1px; bottom: 1px; width: 1px; background: rgba(255,255,255,0.35); }
            .water-reflect:before { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(255,255,255,0.18), rgba(255,255,255,0) 40%); pointer-events: none; }
            .pill-group a { border: 1px solid var(--card-border); }
            /* Small vertical water for calendar day cells */
            .water-xs { position: relative; width: 16px; height: 30px; border: 1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.06); border-radius: 4px; overflow: hidden; box-shadow: inset 0 1px 0 rgba(255,255,255,0.08), 0 4px 12px rgba(0,0,0,0.35); }
            .water-xs .water-fill { box-shadow: inset 0 1px 0 rgba(255,255,255,0.18), 0 0 8px rgba(59,130,246,0.35); }
            .water-xs .water-fill::after { content: ""; position: absolute; left: 0; right: 0; top: 0; height: 2px; background: linear-gradient(180deg, rgba(255,255,255,0.7), rgba(255,255,255,0)); }
            .over-badge { position: absolute; top: -8px; right: -6px; font-size: 9px; color: #93c5fd; text-shadow: 0 1px 2px rgba(0,0,0,0.4); }
            .threshold-line { position: absolute; left: 1px; right: 1px; height: 1px; background: rgba(255,255,255,0.35); }
            .over-glow { filter: drop-shadow(0 0 6px rgba(59,130,246,0.45)); }
            .day-num { font-size: 9px; color: rgba(229,231,235,0.75); margin-top: 2px; text-align: center; }
        </style>
    </head>
    <body class="min-h-screen">
        <header class="fixed top-0 inset-x-0 nav-glass">
            <nav class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-md glass flex items-center justify-center">
                        <div class="w-3 h-3 rounded-sm bg-blue-400"></div>
                    </div>
                    <span class="text-sm font-semibold tracking-wide text-slate-200">Shiftclock</span>
                </div>
                <ul class="flex items-center gap-2 sm:gap-4 text-sm">
                    <?php
                    $links = [
                        'index.php' => 'Overview',
                        'missing.php' => 'Accumulated',
                        'workweek.php' => 'Workweek',
                        'calendar.php' => 'Calendar',
                    ];
                    foreach ($links as $href => $label):
                        $active = $current === $href;
                        ?>
                        <li>
                            <a href="<?= $href ?>" aria-current="<?= $active ? 'page' : 'false' ?>"
                               class="relative px-3 py-1.5 rounded-md <?php echo $active ? 'glass text-white' : 'text-slate-300 hover:text-white hover:bg-white/5'; ?>">
                                <?= htmlspecialchars($label) ?>
                                <?php if ($active): ?>
                                    <span class="absolute -bottom-[6px] left-1/2 -translate-x-1/2 w-6 h-[2px] bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full"></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </header>

        <main class="pt-20 pb-12 animate-[fadeIn_.35s_ease]">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <?php $mainContent($now); ?>
            </div>
        </main>

        
    </body>
    </html>
    <?php
}
?>

<style>
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px);} to { opacity:1; transform:none; } }
</style>
