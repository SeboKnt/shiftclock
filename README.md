# shiftclock

PHP app for tracking time.

## Quick Start

Run locally with PHP's built-in server:

```bash
php -S localhost:8080
```

Then open:

- http://localhost:8080/index.php
- http://localhost:8080/missing.php
- http://localhost:8080/workweek.php
- http://localhost:8080/calendar.php

## Pages

- Overview (index.php): large D/H counter and index.
- Accumulated (missing.php): single prominent long-term metric.
- Workweek (workweek.php): weekly value 
- Calendar (calendar.php): 365-day.

## Data

Replace placeholder variables with your MySQL-backed values later:

- `$total_seconds` on index.php and missing.php
- `$weekly_hours` and `$benchmark` on workweek.php
- `$calendar_data` (date => intensity 0..4) on calendar.php

## Design

Tailwind CSS via CDN, Inter font, dark radial gradient background, glass cards with blur and subtle borders, mobile-responsive layout.