<?php

namespace app\modules\module_block_main_theme\ext;

class Theme
{
  protected $Db, $General, $Modules, $Translate;

  public function __construct($Db, $General, $Modules, $Translate)
  {
    $this->Db = $Db;
    $this->General = $General;
    $this->Modules = $Modules;
    $this->Translate = $Translate;
  }

  public function getMainTheme()
  {
    return json_decode(@file_get_contents(TEMPLATES . 'neo_remastered/colors.json'), true) ?: [];
  }

  public function getCurrentColors()
  {
    $colors = [];
    $currentColors = $this->getMainTheme();
    $colors = [
      'stars' => $currentColors['--stars'] ?? null,
      'span' => $currentColors['--span'] ?? null,
      'span-low' => $currentColors['--span-low'] ?? null,
      'span-10' => $currentColors['--span-10'] ?? null,
      'span-middle' => $currentColors['--span-middle'] ?? null,
      'span-half' => $currentColors['--span-half'] ?? null,
      'bg' => $currentColors['--bg'] ?? null,
      'card' => $currentColors['--card'] ?? null,
      'bg-modal' => $currentColors['--bg-modal'] ?? null,
      'tooltip' => $currentColors['--tooltip'] ?? null,
      'money' => $currentColors['--money'] ?? null,
      'money-bg' => $currentColors['--money-bg'] ?? null,
      'button' => $currentColors['--button'] ?? null,
      'button-hover' => $currentColors['--button-hover'] ?? null,
      'text-default' => $currentColors['--text-default'] ?? null,
      'text-custom' => $currentColors['--text-custom'] ?? null,
      'text-secondary' => $currentColors['--text-secondary'] ?? null,
      'text-default-invert' => $currentColors['--text-default-invert'] ?? null,
      'input-form' => $currentColors['--input-form'] ?? null,
      'btn-disabled' => $currentColors['--btn-disabled'] ?? null,
    ];
    return $colors;
  }

  public function getThemes(string $type = 'default')
  {
    $path = TEMPLATES . 'neo_remastered/themes/' . $type . '/';
    if (!is_dir($path)) {
      return [];
    }

    $files = glob($path . 'theme-*.json');
    if (!$files) {
      return [];
    }

    natsort($files);
    $themes = [];
    foreach ($files as $file) {
      $key = basename($file, '.json');
      $raw = @file_get_contents($file);
      if ($raw === false) {
        $themes[$key] = ['name' => ucfirst(str_replace('-', ' ', $key)), 'colors' => []];
        continue;
      }
      $data = json_decode($raw, true);
      if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        $themes[$key] = ['name' => ucfirst(str_replace('-', ' ', $key)), 'colors' => []];
        continue;
      }
      if (!isset($data['colors'])) {
        $themes[$key] = [
          'name' => $data['name'] ?? ucfirst(str_replace('-', ' ', $key)),
          'colors' => $data
        ];
      } else {
        $themes[$key] = [
          'name' => $data['name'] ?? ucfirst(str_replace('-', ' ', $key)),
          'colors' => is_array($data['colors']) ? $data['colors'] : []
        ];
      }
    }
    return $themes;
  }

  public function getTheme(int $name, string $type = 'default')
  {
    $file = TEMPLATES . 'neo_remastered/themes/' . $type . '/theme-' . $name . '.json';
    if (!is_file($file)) return [];
    $raw = @file_get_contents($file);
    if ($raw === false) return [];
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) return [];
    if (!isset($data['colors'])) {
      return [
        'name' => $data['name'] ?? ucfirst(str_replace('-', ' ', $name)),
        'colors' => $data
      ];
    }
    return [
      'name' => $data['name'] ?? ucfirst(str_replace('-', ' ', $name)),
      'colors' => is_array($data['colors']) ? $data['colors'] : []
    ];
  }

  public function changeTheme(int $theme, string $type = 'default')
  {
    if (!file_exists(TEMPLATES . 'neo_remastered/themes/' . $type . '/theme-' . $theme . '.json')) {
      return [
        'status' => 'error',
        'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_theme_not_found')
      ];
    }
    $Main = $this->getMainTheme();
    foreach ($this->getTheme($theme, $type)['colors'] as $key => $value) {
      $Main[$key] = $value;
    }
    file_put_contents(
      TEMPLATES . 'neo_remastered/colors.json',
      json_encode($Main, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );

    return [
      'status' => 'success',
      'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_theme_changed'),
      'colors' => $this->getTheme($theme, $type)['colors']
    ];
  }

  public function restoreDefaultTheme()
  {
    $backup = json_decode(@file_get_contents(TEMPLATES . 'neo_remastered/backup.json'), true);
    if ($backup) {
      file_put_contents(
        TEMPLATES . 'neo_remastered/colors.json',
        json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
      );
      return [
        'status' => 'success',
        'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_theme_restored')
      ];
    } else {
      return [
        'status' => 'error',
        'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_theme_restore_error')
      ];
    }
  }
  public function changeColors(array $colors)
  {
    $Main = $this->getMainTheme();
    foreach ($colors as $key => $value) {
      if (isset($Main['--' . $key])) {
        $Main['--' . $key] = $value;
        if ($key === "span") {
          $Main['--span-low'] = $this->hex2rgba($value, 0.05);
          $Main['--span-10'] = $this->hex2rgba($value, 0.1);
          $Main['--span-middle'] = $this->hex2rgba($value, 0.25);
          $Main['--span-half'] = $this->hex2rgba($value, 0.5);
        } elseif ($key === "money") {
          $Main['--money-bg'] = $this->hex2rgba($value, 0.1);
        }
      }
    }

    file_put_contents(
      TEMPLATES . 'neo_remastered/colors.json',
      json_encode($Main, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );

    return [
      'status' => 'success',
      'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_colors_changed'),
      'colors' => $this->getCurrentColors()
    ];
  }

  public function savePalette(array $colors)
  {
    $palette_colors = [];
    foreach ($colors as $key => $value) {
      $palette_colors['--' . $key] = $value;
      if ($key === "span") {
        $palette_colors['--span-low'] = $this->hex2rgba($value, 0.05);
        $palette_colors['--span-10'] = $this->hex2rgba($value, 0.1);
        $palette_colors['--span-middle'] = $this->hex2rgba($value, 0.25);
        $palette_colors['--span-half'] = $this->hex2rgba($value, 0.5);
      } elseif ($key === "money") {
        $palette_colors['--money-bg'] = $this->hex2rgba($value, 0.1);
      }
    }

    if (!is_dir(TEMPLATES . 'neo_remastered/themes/custom/')) {
      mkdir(TEMPLATES . 'neo_remastered/themes/custom/', 0755, true);
    }
    $count = count(glob(TEMPLATES . 'neo_remastered/themes/custom/theme-*.json')) + 1;
    $palette = [
      'name' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_palette_number') . ' ' . $count,
      'colors' => $palette_colors
    ];
    file_put_contents(
      TEMPLATES . 'neo_remastered/themes/custom/theme-' . $count . '.json',
      json_encode($palette, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
    if (!file_exists(TEMPLATES . 'neo_remastered/themes/custom/theme-' . $count . '.json')) {
      return [
        'status' => 'error',
        'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_palette_save_error')
      ];
    }
    return [
      'status' => 'success',
      'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_palette_saved'),
      'palette' => $palette
    ];
  }
  public function deleteTheme($theme)
  {
    $file = TEMPLATES . 'neo_remastered/themes/custom/' . $theme . '.json';
    if (!is_file($file)) {
      return [
        'status' => 'error',
        'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_theme_not_found')
      ];
    }
    if (@unlink($file)) {
      return [
        'status' => 'success',
        'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_theme_deleted')
      ];
    } else {
      return [
        'status' => 'error',
        'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_theme_delete_error')
      ];
    }
  }

  public function getBackgroundSettings()
  {
    $jsonFile = MODULESCACHE . 'template_neo/background.json';
    if (!file_exists($jsonFile)) {
      return [
        'type' => '1',
        'image' => null,
        'gradients' => '1',
        'gradients-stars' => '1',
      ];
    }
    $backgroundSettings = json_decode(file_get_contents($jsonFile), true);
    return array_merge(
      [
        'type' => '1',
        'image' => null,
        'gradients' => '1',
        'gradients-stars' => '1',
      ],
      is_array($backgroundSettings) ? $backgroundSettings : []
    );
  }

  public function saveBackground($post)
  {
    chmod(MODULESCACHE . 'template_neo/background.json', 0755);
    $jsonFile = MODULESCACHE . 'template_neo/background.json';
    $backgroundSettings = json_decode(file_get_contents($jsonFile), true);
    $type = $post['background'] ?? '1';
    if ($type == '2') {
      if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] != UPLOAD_ERR_NO_FILE) {
        if (empty($_FILES['background_image']) || $_FILES['background_image']['error'] !== UPLOAD_ERR_OK) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_emptyImages')];
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $paths = [];
        $uploadDir = TEMPLATES . 'neo_remastered/assets/img/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_errorUpload')];
        $ext = strtolower(pathinfo($_FILES['background_image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_errorUpload')];
        $fileName = 'background.' . $ext;
        $backgroundSettings['image'] = $uploadDir . $fileName;
        if (!move_uploaded_file($_FILES['background_image']['tmp_name'], $backgroundSettings['image'])) {
          foreach ($paths as $p) @unlink($p);
          return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_errorUpload')];
        }
      }
    }
    $backgroundSettings = [
      'type' => $type,
      'image' => $backgroundSettings['image'] ?? null,
      'gradients' => $post['gradients'] ?? '1',
      'gradients-stars' => $post['gradients-stars'] ?? '1',
    ];
    $backgroundSettings = array_filter($backgroundSettings);
    $saved = file_put_contents($jsonFile, json_encode($backgroundSettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if ($saved === false) {
      return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_errorWritingFile')];
    }
    return [
      'status' => 'success',
      'text' => $this->Translate->get_translate_module_phrase('module_block_main_theme', '_backgroundSaved'),
      'update_background' => true,
      'type' => $type,
      'image' => $backgroundSettings['image'] ?? null,
      'gradients' => $post['gradients'] ?? '1',
      'gradients-stars' => $post['gradients-stars'] ?? '1',

    ];
  }

  private function hex2rgba($hex, $alpha = 1)
  {
    $original = $hex;
    if (!is_string($hex)) {
      return $original;
    }
    $hex = trim($hex);
    if (!preg_match('/^#?([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $hex)) {
      return $original;
    }

    $hex = ltrim($hex, '#');

    if (strlen($hex) === 3) {
      $r = hexdec(str_repeat($hex[0], 2));
      $g = hexdec(str_repeat($hex[1], 2));
      $b = hexdec(str_repeat($hex[2], 2));
    } else {
      $r = hexdec(substr($hex, 0, 2));
      $g = hexdec(substr($hex, 2, 2));
      $b = hexdec(substr($hex, 4, 2));
    }

    $alpha = is_numeric($alpha) ? (float)$alpha : 1;
    if ($alpha < 0) $alpha = 0;
    if ($alpha > 1) $alpha = 1;

    return 'rgb(' . $r . ', ' . $g . ', ' . $b . ', ' . rtrim(rtrim(number_format($alpha, 3, '.', ''), '0'), '.') . ')';
  }
}
