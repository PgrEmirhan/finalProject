
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



