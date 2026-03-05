<?php
declare(strict_types=1);

function generateWorkSchedule(int $year, int $month): array
{
    $year = (int)$year;
    $month = (int)$month;
    
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $schedule = [];
    
    $nonWorkingDays = 2;
    
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $date = DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
        $dayOfWeek = (int)$date->format('w'); // 0 — воскресенье, 6 — суббота
        
        // Проверяем, является ли день выходным (суббота или воскресенье)
        $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
        
        if ($isWeekend) {
            // Если это выходной день недели — автоматически выходной
            $schedule[] = [
                'day' => $day,
                'isWorkDay' => false
            ];
            $nonWorkingDays++;
        } elseif ($nonWorkingDays < 2) {
            // Если ещё не набралось два выходных дня — выходной
            $schedule[] = [
                'day' => $day,
                'isWorkDay' => false
            ];
            $nonWorkingDays++;
        } else {
            // Иначе — рабочий день, сбрасываем счётчик выходных
            $schedule[] = [
                'day' => $day,
                'isWorkDay' => true
            ];
            $nonWorkingDays = 0;
        }
    }
    
    return $schedule;
}

// Вывод расписания указанного месяца
function displaySchedule(int $year, int $month, array $schedule): void
{
    $monthName = [
        1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
        5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
        9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
    ];

    echo "Расписание на {$monthName[$month]} $year года\n";

    foreach ($schedule as $dayData) {
        $day = $dayData['day'];
        $isWorkDay = $dayData['isWorkDay'];

        if ($isWorkDay) {
            echo "\033[32m$day\033[0m "; // Зелёный цвет для рабочих дней
        } else {
            echo "$day "; // Обычный цвет для выходных
        }

        // Разбиваем на недели (каждые 7 дней)
        if ($day % 7 === 0) {
            echo "\n";
        }
    }
    echo "\n\n";
}

// Основная логика выполнения
$startYear = isset($argv[1]) ? (int)$argv[1] : (int)date('Y');
$startMonth = isset($argv[2]) ? (int)$argv[2] : (int)date('n');
$monthsCount = isset($argv[3]) ? (int)$argv[3] : 1;

for ($i = 0; $i < $monthsCount; $i++) {
    $currentMonth = $startMonth + $i;
    $currentYear = $startYear;

    // Корректировка года и месяца при переходе через декабрь
    if ($currentMonth > 12) {
        $currentMonth -= 12;
        $currentYear++;
    }

    $schedule = generateWorkSchedule($currentYear, $currentMonth);
    displaySchedule($currentYear, $currentMonth, $schedule);
}