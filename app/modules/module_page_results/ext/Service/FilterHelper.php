<?php

namespace app\modules\module_page_results\ext\Service;

final class FilterHelper
{
  public static function normalizeArrayFilter($value): array
  {
    if (!is_array($value)) $value = [$value];
    return array_map('strval', array_filter($value, fn($v) => $v !== '' && $v !== null));
  }

  public static function matchesArrayFilter(array $filter, array $values): bool
  {
    if (empty($filter) || (isset($filter[0]) && $filter[0] === '0')) return true;
    $values = array_map('strval', array_filter(array_map('trim', $values)));
    return count(array_intersect($values, $filter)) > 0;
  }
}
