<?php
function ts_bounds_from_label(string $ym): array {
// período 25..24
$first = new DateTime("{$ym}-25 00:00:00"); // 25 do mês-ym
$start = (clone $first)->modify('-1 month'); // 25 do mês anterior
$end   = (clone $first)->modify('-1 second'); // 24 23:59:59 do ym
return [$start, $end];
}
function ts_lock_at(string $ym): DateTime {
// bloqueia a 25 00:00:00 do ym
return new DateTime("{$ym}-25 00:00:00");
}

function ot_bounds_from_month(string $ym): array {
    $start = new DateTime("{$ym}-01 00:00:00");
    $end   = (clone $start)->modify('last day of this month 23:59:59');
    return [$start, $end];
}

function ot_deadline_at(string $ym): DateTime {
    // fecha às 00:00 do dia 11 do mês seguinte (M+1)
    $d = new DateTime("{$ym}-11 00:00:00");
    $d->modify('+1 month');
    return $d;
}

function ot_is_open_for_day(string $dia): bool {
    $ym = substr($dia, 0, 7);
    // aberto apenas enquanto "agora" for ANTES do deadline
    return (new DateTime()) < ot_deadline_at($ym);
}
