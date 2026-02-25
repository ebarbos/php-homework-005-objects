<?php
declare(strict_types=1);

// Расписание выхода на работу сотрудника (сутки через двое) с учётом переноса выходных

function generateWorkSchedule($year, $month) {
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $schedule = [];
    $isWorkingDay = true; // Первое число — рабочий день
    $restCounter = 0; // Счётчик выходных

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $date = DateTime::createFromFormat('Y-m-d', "$year-$month-$day");
        $dayOfWeek = (int)$date->format('w'); // 0 — воскресенье, ..., 6 — суббота

        $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6); // Суббота или воскресенье
        $isWorkDayFlag = false;

        if ($isWorkingDay && !$isWeekend) {
            $isWorkDayFlag = true;
            $restCounter = 0;
            $isWorkingDay = false; // Следующий день — выходной
        } elseif ($isWorkingDay && $isWeekend) {
            // Переносим рабочий день на ближайший понедельник
            $nextMonday = clone $date;
            $daysToAdd = (8 - $dayOfWeek) % 7;
            if ($daysToAdd === 0) $daysToAdd = 7;
            $nextMonday->modify("+$daysToAdd days");

            // Проверка, что понедельник в том же месяце
            if ((int)$nextMonday->format('m') === $month) {
                $mondayDay = (int)$nextMonday->format('d');
                // Помечаем понедельник как рабочий день
                $schedule[$mondayDay - 1] = [
                    'day' => $mondayDay,
                    'isWorkDay' => true
                ];
            }
            $restCounter = 0;
            $isWorkingDay = false;
        } else {
            $restCounter++;
            if ($restCounter >= 2) {
                $isWorkingDay = true;
                $restCounter = 0;
            }
        }

        // Если день ещё не был обработан как перенесённый, добавляем его в расписание
        if (!isset($schedule[$day - 1])) {
            $schedule[$day - 1] = [
                'day' => $day,
                'isWorkDay' => $isWorkDayFlag
            ];
        }
    }

    return $schedule;
}


// Вывод расписания указанного месяца
function displaySchedule($year, $month, $schedule) {
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
$startYear = isset($argv[1]) ? (int)$argv[1] : date('Y');
$startMonth = isset($argv[2]) ? (int)$argv[2] : date('n');
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