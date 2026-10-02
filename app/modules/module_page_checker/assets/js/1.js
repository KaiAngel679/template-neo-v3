const swiper = new Swiper('.swiper', {
  loop: true,
  slidesPerView: 'auto',
  autoplay: {
    delay: 5000,
    disableOnInteraction: true
  },
  centeredSlides: true,
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
  mousewheel: {
    sensitivity: 1,
    eventsTarget: ".checker__swiper",
  },
  keyboard: {
    enable: true,
    onlyInViewport: true,
    pageUpDown: true,
  },
  preloadImages: false,
  lazy: {
    loadOnTransitionStart: false,
    loadPrevNext: false,
  },
  watchOverflow: true,
  grabCursor: true,
  simulateTouch: true,
});

FilePond.registerPlugin(
  FilePondPluginImagePreview,
  FilePondPluginFileValidateSize,
  FilePondPluginFileValidateType
);

const filepondElement = document.querySelector('.filepond-single');
if (filepondElement) {
  pond = FilePond.create(filepondElement, {
    allowMultiple: false,
    allowImagePreview: false,
    credits: false,
    maxFileSize: '50MB',
    acceptedFileTypes: [
      'application/x-msdownload',
      'application/x-msdos-program',
      'application/zip',
      'application/x-zip-compressed',
      'application/vnd.rar',
      'application/x-rar-compressed'
    ],
    labelIdle: get_translate_module_phrase('module_page_checker', '_uploadZip'),
    server: {
      url: location.href,
      process: {
        method: 'POST',
        ondata: (formData) => {
          formData.append('send_filepond_file', 'true');
          formData.append('type', 'file');
          return formData;
        },
        onload: (result) => {
          const res = JSON.parse(result);
          const filepondInput = document.getElementById('filepond-single');
          let currentFiles = filepondInput.value;
          currentFiles = currentFiles ? currentFiles + ';' + res.file : res.file;
          filepondInput.value = currentFiles;
          return res.file;
        }
      },
      revert: (fileId, load) => {
        fetch(location.href, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ file: fileId, type: 'file' })
        })
          .then(() => {
            const filepondInput = document.getElementById('filepond-single');
            let currentFiles = filepondInput.value.split(';');
            currentFiles = currentFiles.filter(f => f !== fileId);
            filepondInput.value = currentFiles.join(';');
            load();
          });
      }
    }
  });
}

const filepondElementImg = document.querySelector('.filepond-multiple');
if (filepondElementImg) {
  pond = FilePond.create(filepondElementImg, {
    allowMultiple: true,
    allowImagePreview: true,
    credits: false,
    maxFileSize: '5MB',
    acceptedFileTypes: ['image/*'],
    labelIdle: get_translate_module_phrase('module_page_checker', '_uploadFour'),
    server: {
      url: location.href,
      process: {
        method: 'POST',
        ondata: (formData) => {
          formData.append('send_filepond_file', 'true');
          formData.append('type', 'img');
          return formData;
        },
        onload: (result) => {
          const res = JSON.parse(result);
          const filepondInput = document.getElementById('filepond-multiple');
          let currentFiles = filepondInput.value;
          currentFiles = currentFiles ? currentFiles + ';' + res.file : res.file;
          filepondInput.value = currentFiles;
          return res.file;
        }
      },
      revert: (fileId, load) => {
        fetch(location.href, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ file: fileId, type: 'img' })
        })
          .then(() => {
            const filepondInput = document.getElementById('filepond-multiple');
            let currentFiles = filepondInput.value.split(';');
            currentFiles = currentFiles.filter(f => f !== fileId);
            filepondInput.value = currentFiles.join(';');
            load();
          });
      }
    }
  });
}

function sendRequest(body) {
  return $.ajax({
    url: location.href,
    type: 'POST',
    data: body,
    dataType: 'json'
  });
}

$('#save-one').click(function () {
  sendRequest({ save_one: true, auth: $('#access').is(':checked') ? 1 : 0, url_vt: $('#vtLink').val(), url_ft: $('#shareLink').val(), name_checker: $('#checkerName').val(), description_checker: $('#checkerContent').val(), file: $('#filepond-single').val() })
    .done(function (result) {
      if (result.status == 'success' && result.url == 'reload') {
        location.reload();
      }
    });
});

const $addBtn = $('#add-paragraph');
const $container = $addBtn.closest('.card-container');
$addBtn.on('click', function () {
  const $first = $container.find('.checker__paragraph').first();
  if (!$first.length) return;
  const $clone = $first.clone(false, false);
  $clone.find('.paragraph-title').val('');
  $clone.find('.paragraph-text').val('');
  $clone.insertBefore($('#hideImg'));
});
$(document).on('click', '.paragraph-delete', function () {
  const $all = $('.checker__paragraph');
  if ($all.length === 1) {
    $all.find('.paragraph-title, .paragraph-text').val('');
    return;
  }
  $(this).closest('.checker__paragraph').remove();
});

$('#save-two').on('click', function () {
  const paragraphs = [];
  $('.checker__paragraph').each(function () {
    const title = $(this).find('.paragraph-title').val()?.trim() || '';
    const text = $(this).find('.paragraph-text').val()?.trim() || '';
    if (title !== '' || text !== '') {
      paragraphs.push({ title, text });
    }
  });

  const hideImg = $('#hideImg').is(':checked') ? 1 : 0;
  const imgs = $('#filepond-multiple').val();
  sendRequest({ save_two: true, slider: hideImg, paragraphs: JSON.stringify(paragraphs), img: imgs })
    .done(function (result) {
      if (result.status === 'success' && result.url === 'reload') {
        location.reload();
      } else if (result.status === 'error') {
        noty(result.text, result.status);
      }
    });
});

function setDownloadBtn($btn, url) {
  if (!$btn.length) return;
  $btn.data('href', url).prop('disabled', false);
}

function applyDownloadLinks(links) {
  setDownloadBtn($('.download-btn-site'), links.site || '');
  setDownloadBtn($('.download-btn-share'), links.share || '');
}

$(document).on('change', '#read', function () {
  const checked = this.checked;
  const $all = $('.download-btn');
  if (!checked) {
    $all.each(function () { $(this).prop('disabled', true).data('href', '') });
    return;
  }
  $all.prop('disabled', true);
  sendRequest({ get_links: true })
    .done(function (result) {
      applyDownloadLinks(result.links);
    })
});

$(document).on('click', '.download-btn', function () {
  const $btn = $(this);
  const href = $btn.data('href');
  if (/^https?:\/\//i.test(href)) window.open(href, '_blank');
  else window.location.href = href;
});

const $hideImg = $('#hideImg');
const $imgInfoSpan = $hideImg
  .closest('.card-container')
  .find('span')
const $filepondInput = $('.filepond-multiple');

function toggleImagesBlock() {
  const hide = $hideImg.is(':checked');
  const pondRoot = $filepondInput.next('.filepond--root');
  if (hide) {
    $imgInfoSpan.hide();
    if (pondRoot.length) pondRoot.hide(); else $filepondInput.hide();
  } else {
    $imgInfoSpan.show();
    if (pondRoot.length) pondRoot.show(); else $filepondInput.show();
  }
}
$hideImg.off('change.toggleImagesBlock').on('change', toggleImagesBlock);
toggleImagesBlock();

Fancybox.bind('[data-fancybox="gallery"]');

$('#delete-file').click(function () {
  sendRequest({ delete: true, type: 'file' })
    .done(function (result) {
      if (result.status == 'success' && result.url == 'reload') {
        location.reload();
      }
    });
});

$('#delete-photo').click(function () {
  sendRequest({ delete: true, type: 'img' })
    .done(function (result) {
      if (result.status == 'success' && result.url == 'reload') {
        location.reload();
      }
    });
});