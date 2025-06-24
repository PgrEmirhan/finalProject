
 document.addEventListener('DOMContentLoaded', () => {
  const isDarkMode = localStorage.getItem('darkMode');
  if (isDarkMode === 'enabled') {
    document.body.classList.add('dark-mode');
  }
  updateLogo(); 
function updateLogo() {
  const logo = document.getElementById('logo');
  const isDarkMode = document.body.classList.contains('dark-mode');
  if (logo) {
    logo.src = isDarkMode ? 'images/logo-1.png' : 'images/logo.png';
  }
}

document.getElementById('dark-mode-toggle-desktop').addEventListener('click', () => {
  document.body.classList.toggle('dark-mode'); 
    updateLogo();

  if (document.body.classList.contains('dark-mode')) {
    localStorage.setItem('darkMode', 'enabled');
  } else {
    localStorage.setItem('darkMode', 'disabled');
  }
});

document.getElementById('dark-mode-toggle-mobile').addEventListener('click', () => {
  document.body.classList.toggle('dark-mode'); 
  updateLogo(); 

  if (document.body.classList.contains('dark-mode')) {
    localStorage.setItem('darkMode', 'enabled');
  } else {
    localStorage.setItem('darkMode', 'disabled');
  }
});
  const logoLink = document.getElementById("logo");

  if (logoLink) {
    logoLink.addEventListener("click", function (e) {
      e.preventDefault(); 

      const confirmLogout = confirm("Çıkış yapmak istediğinize emin misiniz?");
      if (confirmLogout) {
        window.location.href = "logout.php?redirect=index.php";
      }
    });
  }


  const avatarBtn = document.getElementById('avatarBtn');
  const dropdown = document.getElementById('dropdownMenu');

  avatarBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
  });

  document.addEventListener('click', function () {
    dropdown.style.display = 'none';
  });


  document.getElementById("shareForm")?.addEventListener("submit", function (event) {
    event.preventDefault();

    const recipient = document.getElementById("recipient").value;
    const shareLink = document.getElementById("shareLink").value;
    const file_id = document.getElementById("modalFileId").value;

    const data =
      "recipient=" + encodeURIComponent(recipient) +
      "&file_link=" + encodeURIComponent(shareLink) +
      "&file_id=" + encodeURIComponent(file_id) +
      "&shareType=" + encodeURIComponent(document.getElementById("shareType").value) +
      "&password=" + encodeURIComponent(document.querySelector("[name='password']").value) +
      "&expiry_days=" + encodeURIComponent(document.querySelector("[name='expiry_days']").value) +
      "&max_downloads=" + encodeURIComponent(document.querySelector("[name='max_downloads']").value);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "shareFile.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function () {
      if (xhr.status === 200) {
        alert(xhr.responseText);
        closeModal();
      } else {
        alert("Bir hata oluştu.");
      }
    };

    xhr.send(data);
  });

  document.getElementById("showAllBtn")?.addEventListener("click", (e) => {
    e.preventDefault();
    document.querySelector('input[name="type"]').value = '';
    document.querySelector('input[name="min_size"]').value = '';
    document.querySelector('input[name="max_size"]').value = '';
    document.getElementById('filterForm')?.reset();
    window.location.href = "upload.php";
  });

});

function triggerFileInput() {
  document.getElementById('fileInput').click();
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
 
function uploadFile() {
    if (filesToUpload.length === 0) {
        alert('Lütfen bir dosya seçin!');
        return;
    }

    var formData = new FormData();
    formData.append('file', filesToUpload[0]); 

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'upload.php', true);

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
}function openShareModal(fileName, fileId) {
  if (fileName) {
    const shareLink = "http://localhost/finalProject/public/uploads/" + fileName;
    document.getElementById("shareLink").value = shareLink;
    document.getElementById("modalFileId").value = fileId;
    document.getElementById("shareModal").style.display = "block";
        document.getElementById("overlay").style.display = "block";

  } else {
    console.log("Dosya yolu alınamadı.");
  }
}
    
function confirmDelete(fileId) {
    if (confirm('Emin misiniz? Bu dosya kalıcı olarak silinecek?')) {
          const formData = new FormData();
        formData.append('delete_file', fileId);
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'upload.php', true);
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
function copyLink() {
  const copyText = document.getElementById("shareLink");
  copyText.select();
  copyText.setSelectionRange(0, 99999);
  document.execCommand("copy");
  alert("Link panoya kopyalandı: " + copyText.value);
}

function closeModal() {
  document.getElementById("shareModal").style.display = "none";
      document.getElementById("overlay").style.display = "none";

}

function toggleShareOptions() {
  const shareType = document.getElementById("shareType").value;
  const passwordField = document.getElementById("passwordField");
  passwordField.style.display = (shareType === "private") ? "block" : "none";
}
  function openPopup() {
    document.getElementById("popupMenu").style.display = "flex";
  }

  function closePopup() {
    document.getElementById("popupMenu").style.display = "none";
  }

  window.addEventListener("click", function (e) {
    const dropdown = document.getElementById("dropdownMenu");
    const avatarBtn = document.getElementById("avatarBtn");
    if (!dropdown.contains(e.target) && !avatarBtn.contains(e.target)) {
      dropdown.style.display = "none";
    }

    const popup = document.getElementById("popupMenu");
    if (e.target === popup) {
      closePopup();
    }
  });
