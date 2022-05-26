<?php

declare(strict_types=1);

$week = ['пн', 'вт', 'ср' ,'чт', 'пт', 'сб', 'вс'];
$months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
$months_rus = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
  'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь' ];


$getDayWeek = static function(int $year, int $month, $day) use($months): int
{
  $strMonth = $months[$month - 1];
  $strYear = (string)$year;
  $date = DateTime::createFromFormat('j-M-Y', "$day-$strMonth-$strYear");
  return (int)$date->format('N');
};

$createBlankCalendar = static function(int $month, int $year) use($getDayWeek): array
{
  $calendar = [];
  $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
  $dayWeek = $getDayWeek($year, $month, 1);
  for ($i = 1; $i < $dayWeek; $i++) {
    $calendar[] = ["day" => '  ', "dayWeek" => null];
  }
  for ($i = 1; $i <= $daysInMonth; $i++) {
    $dayWeek = $getDayWeek($year, $month, $i);
    $num = $i < 10 ? " $i" : $i;
    $calendar[] = ["day" => $num, "dayWeek" => $dayWeek];
  }
  return $calendar;
};

$scheduleHeader = static function() use($week): void
{
  echo implode(' ', $week);
  echo PHP_EOL;
};

$scheduleBody = static function(array $calendar): void
{
  foreach ($calendar as $i => $iValue) {
    $x = $iValue["day"];
    if ($iValue["dayWeek"] === 6 || $iValue["dayWeek"] === 7) {
      echo "\033[32m$x\033[0m" . (($iValue["dayWeek"] === 7) ? PHP_EOL : ' ');
    } elseif ($iValue["dayWeek"] === "work") {
      echo "\033[31m$x\033[0m" . ' ';
    } else {
      echo $x . ' ';
    }
  }
};

$fillCalendar = static function(int $startDay, array &$calendar): void
{
  $nextWorkDay = $startDay;
  $count = count($calendar);
  for ($i = 0; $i < $count;  $i++) {
    if ($nextWorkDay === (int)$calendar[$i]["day"]) {
      if (($calendar[$i]["dayWeek"] === 6 || $calendar[$i]["dayWeek"] === 7)) {
        ++$nextWorkDay;
      } else {
        $calendar[$i]["dayWeek"] = "work";
        $nextWorkDay += 3;
      }
    }
  }
};

$showCalendar = static function (array $calendar) use ($scheduleBody, $scheduleHeader): void
{
  $scheduleHeader();
  $scheduleBody($calendar);
  echo PHP_EOL;
};

$showInfo = static function (int $month, int $year) use ($months_rus): void
{
  $time = strtotime("1-$month-$year");
  $monthNum = date('n', $time);
  $monthName = $months_rus[$monthNum - 1];
  echo "Расписание рабочих дней на месяц: $monthName $year г.";
  echo PHP_EOL;
  echo PHP_EOL;
  echo "\033[32m Зеленый \033[0m - календарные выходные дни" . PHP_EOL;
  echo "\033[31m Красный \033[0m - Рабочие дни" . PHP_EOL;
  echo PHP_EOL;

};

$calendar = static function (int $shedMonth, int $shedYear) use ($showCalendar, $fillCalendar, $createBlankCalendar, $showInfo): void
{
  echo PHP_EOL;
  $showInfo($shedMonth, $shedYear);
  $calendar = $createBlankCalendar($shedMonth, $shedYear);
  $fillCalendar(1, $calendar);
  $showCalendar($calendar);
  echo PHP_EOL;
};

$calendar(5, 2022);
