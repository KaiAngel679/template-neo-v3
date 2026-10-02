
let dropArea = document.getElementById("drop-area")
  ;['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false)
    document.body.addEventListener(eventName, preventDefaults, false)
  })

  ;['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, highlight, false)
  })

  ;['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, unhighlight, false)
  })
dropArea.addEventListener('drop', handleDrop, false)

function preventDefaults(e) {
  e.preventDefault()
  e.stopPropagation()
}

function highlight(e) {
  dropArea.classList.add('highlight')
}

function unhighlight(e) {
  dropArea.classList.remove('active')
}

function handleDrop(e) {
  var f = e.dataTransfer.files[0];
  document.getElementById('fileElem').files = e.dataTransfer.files
  if (!f.type.match('image.png')) {
    note({
      content: 'Not valid image format! only PNG ',//Текст сообщения
      type: 'error',//staus error, warn, info, success
      time: 2//Время отображения в секундах
    });
    $('#fileElem').val('');
    f.value = '';
    return;
  }
  var reader = new FileReader();
  reader.onload = (function (theFile) {
    return function (e) {
      var img = document.createElement('img');
      img.src = e.target.result;
      document.getElementById('gallery').innerHTML = "";
      document.getElementById('gallery').insertBefore(img, null);
    };
  })(f);
  reader.readAsDataURL(f);
}

function handleFiles(evt) {
  var f = evt.target.files[0];
  if (!f.type.match('image.png')) {
    note({
      content: 'Not valid image format! only PNG ',
      type: 'error',
      time: 2
    });
    $('#fileElem').val('');
    f.value = '';
    return;
  }
  var reader = new FileReader();
  reader.onload = (function (theFile) {
    return function (e) {
      var img = document.createElement('img');
      img.src = e.target.result;
      document.getElementById('gallery').innerHTML = "";
      document.getElementById('gallery').insertBefore(img, null);
    };
  })(f);
  reader.readAsDataURL(f);

}
document.getElementById('fileElem').addEventListener('change', handleFiles, false);