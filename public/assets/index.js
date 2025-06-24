    const words = ["YÜKLEYİN","PAYLAŞIN", "YÖNETİN"];
    let index = 0;
    const wordElement = document.getElementById("word");
    let currentWord = '';
    let letterIndex = 0;

    function typeLetter() {
        if (letterIndex < currentWord.length) {
            wordElement.textContent += currentWord.charAt(letterIndex);
            letterIndex++;
            setTimeout(typeLetter, 100); 
        } else {
            setTimeout(() => { 
                index = (index + 1) % words.length;
                currentWord = words[index];
                letterIndex = 0;
                wordElement.textContent = ''; 
                typeLetter();  
            }, 1500); 
        }
    }
    
    currentWord = words[index];
    typeLetter();  
function copyLink() {
    // Linki buluyoruz (a etiketi içindeki metni alıyoruz)
    const copyText = document.querySelector('.success-msg a').href;

    // Geçici bir input alanı oluşturuyoruz
    const tempInput = document.createElement("input");
    document.body.appendChild(tempInput);

    // Linki input'a yerleştiriyoruz
    tempInput.value = copyText;

    // Input'u seçiyoruz
    tempInput.select();
    tempInput.setSelectionRange(0, 99999); // Mobil uyumluluk için

    // Kopyalama işlemini yapıyoruz
    document.execCommand("copy");

    // Geçici input'u kaldırıyoruz
    document.body.removeChild(tempInput);

    // Kullanıcıya bildirim
    alert("Link panoya kopyalandı: " + copyText);
}
      // Dosya yükleme alanları
  const dropArea = document.getElementById('drop-area');
  const fileInput = document.getElementById('fileInput');
  const progressBar = document.getElementById('progress-bar');
  let filesToUpload = [];

  dropArea.addEventListener('dragover', function (e) {
    e.preventDefault();
    dropArea.classList.add('hover');
  });

  dropArea.addEventListener('dragleave', function (e) {
    e.preventDefault();
    dropArea.classList.remove('hover');
  });

  dropArea.addEventListener('drop', function (e) {
    e.preventDefault();
    dropArea.classList.remove('hover');
    const files = e.dataTransfer.files;
    handleFileSelection(files);
  });

  fileInput.addEventListener('change', function (e) {
    const files = e.target.files;
    handleFileSelection(files);
  });

  function handleFileSelection(files) {
    filesToUpload = files;
    document.querySelector('.upload-btn').disabled = false;
  }
    function triggerFileInput() {
        document.getElementById('fileInput').click();
    }
    
    function uploadFile() {
        var fileInput = document.getElementById('fileInput');
        var file = fileInput.files[0];
        if (!file) {
            alert('Lütfen bir dosya seçin!');
            return;
        }

        var formData = new FormData();
        formData.append('file', file);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php', true);

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                var percent = (e.loaded / e.total) * 100;
                document.getElementById('progress-bar').style.width = percent + '%';
                document.getElementById('progress-bar').textContent = Math.round(percent) + '%';
            }
        });

        xhr.onload = function() {
            if (xhr.status === 200) {
                alert("Dosya başarıyla yüklendi!");
                location.reload();  
            } else {
                alert("Dosya yükleme sırasında bir hata oluştu.");
            }
        };

        xhr.send(formData); 
    }
    
     
function confirmDelete(fileId) {
    if (confirm('Emin misiniz? Bu dosya kalıcı olarak silinecek?')) {
 
        const formData = new FormData();
        formData.append('delete_file', fileId); 

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php', true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                alert('Dosya başarıyla silindi.');
                location.reload();
            } else {
                alert('Dosya silinirken bir hata oluştu.');
            }
        };
        xhr.send(formData);
    }
}

window.addEventListener('DOMContentLoaded', () => {
  const isDarkMode = localStorage.getItem('darkMode');
  if (isDarkMode === 'enabled') {
    document.body.classList.add('dark-mode');
  }
  updateLogo(); // Sayfa yüklendiğinde logoyu da güncelle
});

function updateLogo() {
  const logo = document.getElementById('logo');
  const isDarkMode = document.body.classList.contains('dark-mode');
  if (logo) {
    logo.src = isDarkMode ? 'images/logo-1.png' : 'images/logo.png';
  }
}

// Butona tıklanınca dark mode aç/kapat ve logoyu güncelle
document.getElementById('dark-mode-toggle-desktop').addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');
   updateLogo(); // Sayfa yüklendiğinde logoyu da güncelle

  if (document.body.classList.contains('dark-mode')) {
    localStorage.setItem('darkMode', 'enabled');
  } else {
    localStorage.setItem('darkMode', 'disabled');
  }
});
// Butona tıklanınca dark mode aç/kapat ve logoyu güncelle
document.getElementById('dark-mode-toggle-mobile').addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');
   updateLogo(); // Sayfa yüklendiğinde logoyu da güncelle

  if (document.body.classList.contains('dark-mode')) {
    localStorage.setItem('darkMode', 'enabled');
  } else {
    localStorage.setItem('darkMode', 'disabled');
  }
});
  const banner   = document.getElementById('cookie-banner');
  const acceptBtn = document.getElementById('accept-cookies');

  // Daha önce onay verildiyse banner'ı gizle
  if (localStorage.getItem('tefs-cookies-accepted') === 'yes') {
    banner.style.display = 'none';
  }

  // Kabul Et butonuna tıklanınca onayı kaydet ve gizle
  acceptBtn.addEventListener('click', () => {
    localStorage.setItem('tefs-cookies-accepted', 'yes');
    banner.style.display = 'none';
  }); 
function openPopup() {
  document.getElementById("popupMenu").style.display = "flex";
}

function closePopup() {
  document.getElementById("popupMenu").style.display = "none";
}

// Menü dışına tıklanınca popup kapanır
window.addEventListener("click", function (e) {
  const popup = document.getElementById("popupMenu");
  const popupMenu = document.querySelector(".popup-menu");
  const hamburger = document.querySelector(".hamburger");

  // Eğer popup açıksa ve tıklama popup'ın içine veya hamburger ikonuna değilse kapat
  if (popup.style.display === "flex" && !popupMenu.contains(e.target) && !hamburger.contains(e.target)) {
    closePopup();
  }
}); 