 // Sayfa yüklendiğinde localStorage'dan dark mode'u kontrol et
 document.addEventListener('DOMContentLoaded', () => {
  // Dark mode kontrolü
  const isDarkMode = localStorage.getItem('darkMode');
  if (isDarkMode === 'enabled') {
    document.body.classList.add('dark-mode');
  }
  updateLogo(); // Sayfa yüklendiğinde logoyu da güncelle
function updateLogo() {
  const logo = document.getElementById('logo');
  const isDarkMode = document.body.classList.contains('dark-mode');
  if (logo) {
    logo.src = isDarkMode ? 'images/logo-1.png' : 'images/logo.png';
  }
}

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
});
 const logoLink = document.getElementById("logo");

  if (logoLink) {
    logoLink.addEventListener("click", function (e) {
      e.preventDefault(); // normal yönlendirmeyi durdur

      const confirmLogout = confirm("Çıkış yapmak istediğinize emin misiniz?");
      if (confirmLogout) {
        window.location.href = "logout.php?redirect=index.php";
      }
    });
  }
  // Avatar dropdown
  const avatarBtn = document.getElementById('avatarBtn');
  const dropdown = document.getElementById('dropdownMenu');

  avatarBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
  });

  document.addEventListener('click', function () {
    dropdown.style.display = 'none';
  });
  // Hamburger popup
  // Hamburger popup
  function openPopup() {
    document.getElementById("popupMenu").style.display = "flex";
  }

  function closePopup() {
    document.getElementById("popupMenu").style.display = "none";
  }

  // Menü dışına tıklanınca dropdown kapanır
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