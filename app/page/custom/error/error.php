<!doctype html>
<html lang="ru">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Error</title>
</head>
<link rel="stylesheet" href="<?= '//' . $_SERVER["SERVER_NAME"] ?>/storage/assets/css/style.css">
<link rel="stylesheet" href="<?= '//' . $_SERVER["SERVER_NAME"] ?>/app/templates/neo_remastered/assets/css/style.css">
<link rel="stylesheet" href="<?= '//' . $_SERVER["SERVER_NAME"] ?>/app/page/custom/install/assets/css/style.css">
<style>
  <?php
  $colorsJsonPath = $_SERVER['DOCUMENT_ROOT'] . '/app/templates/neo_remastered/colors.json';
  $css = ':root { }';
  if (is_file($colorsJsonPath)) {
    $json = file_get_contents($colorsJsonPath);
    $vars = json_decode($json, true);
    if (is_array($vars)) {
      $lines = [];
      foreach ($vars as $name => $value) {
        $lines[] = "  {$name}: {$value};";
      }
      $css = ":root {\n" . implode("\n", $lines) . "\n}";
    }
  }
  echo $css;
  ?>
</style>

<body>
  <div class="global-container">
    <div class="container-fluid error-container">
      <div class="row">
        <div class="col-md-12">
          <div class="error_content">
            <div class="error-image">
              <img src="/storage/cache/img/error/gear.webp" alt="">
            </div>
            <div class="error_texts_block error-modal">
              <div class="error_oops">
                <button onclick="history.back();return false;" class="error_arrow">
                  <svg>
                    <use href="/resources/img/sprite.svg#single-chevrone-left"></use>
                  </svg>
                </button>
                Broken 😭
              </div>
              <div class="error_code"><?= isset($code) ? htmlentities($code) : '500' ?></div>
              <div class="description"><?= isset($file) ? htmlentities($file) :  'Exception script, check web server logs' ?></div>
              <?php if (isset($error) && !empty($error) && isset($_SESSION['user_admin'])) : ?>
                <pre class="error-trace"><code><?= $error ?></code></pre>
              <?php endif; ?>
              <hr>
              <div class="flex-inline width-100">
                <?php if (isset($error) && !empty($error) && isset($_SESSION['user_admin'])) : ?>
                  <button class="width-100 active" id="copyTrace">
                    <svg>
                      <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    Copy trace
                  </button>
                <?php endif; ?>
                <button class="width-100" onclick="window.top.location.href = 'https://onevalve.ru'">onevalve.ru</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
<script>
  document.querySelectorAll('pre code').forEach(function(block) {
    var raw = block.querySelector('*') ? block.innerHTML : (block.textContent || '');
    raw = raw.replace(/^[\s\r\n]*\n/, '').replace(/\n[\s\r\n]*$/, '');
    var lines = raw.split('\n');
    var minIndent = null;
    for (var i = 0; i < lines.length; i++) {
      var line = lines[i];
      if (line.trim().length === 0) continue;
      var m = line.match(/^[ \t]*/);
      var indent = m ? m[0].length : 0;
      if (minIndent === null || indent < minIndent) minIndent = indent;
    }
    if (minIndent && minIndent > 0) {
      var stripRe = new RegExp('^[ \t]{' + minIndent + '}');
      lines = lines.map(function(l) {
        return l.replace(stripRe, '');
      });
    }

    block.textContent = lines.join('\n');

    var pre = block.closest('pre.error-trace');
    if (pre) {
      var icon = document.getElementById('copyTrace');
      if (icon) {
        icon.classList.add('copy-btn');
        icon.setAttribute('data-clipboard-text', block.textContent);
        if (!icon.dataset.copyBound) {
          icon.addEventListener('click', function() {
            var text = icon.getAttribute('data-clipboard-text') || '';
            if (!text) {
              var codeEl = document.querySelector('pre.error-trace code');
              text = codeEl ? codeEl.textContent : '';
            }
            if (!text) return;

            function fallbackCopy(t) {
              var ta = document.createElement('textarea');
              ta.value = t;
              ta.setAttribute('readonly', '');
              ta.style.position = 'fixed';
              ta.style.opacity = '0';
              document.body.appendChild(ta);
              ta.focus();
              ta.select();
              try {
                document.execCommand('copy');
              } catch (e) {}
              document.body.removeChild(ta);
            }

            if (navigator.clipboard && window.isSecureContext) {
              navigator.clipboard.writeText(text).catch(function() {
                fallbackCopy(text);
              });
            } else {
              fallbackCopy(text);
            }
          });
          icon.dataset.copyBound = '1';
        }
      }
    }
  });
</script>

</html>