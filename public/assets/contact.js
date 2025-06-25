document.addEventListener('DOMContentLoaded', () => {
  const isDarkMode = localStorage.getItem('darkMode');
  if (isDarkMode === 'enabled') {
    document.body.classList.add('dark-mode');
  } 
  updateLogo();

  function updateLogo() {
    const logo = document.getElementById('logo');
    const isDark = document.body.classList.contains('dark-mode');
    if (logo) {
      logo.src = isDark ? 'images/logo-1.png' : 'images/logo.png';
    }
  }

  const darkModeDesktopBtnG = document.getElementById('dark-mode-toggle-desktop-guest');
  const darkModeDesktopBtnU = document.getElementById('dark-mode-toggle-desktop-user');
  const darkModeMobileBtn = document.getElementById('dark-mode-toggle-mobile');

  function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    updateLogo();
    localStorage.setItem('darkMode', document.body.classList.contains('dark-mode') ? 'enabled' : 'disabled');
  }

  if (darkModeDesktopBtnG) {
    darkModeDesktopBtnG.addEventListener('click', toggleDarkMode);
  }

  if (darkModeDesktopBtnU) {
    darkModeDesktopBtnU.addEventListener('click', toggleDarkMode);
  }

  if (darkModeMobileBtn) {
    darkModeMobileBtn.addEventListener('click', toggleDarkMode);
  }

const logoLink = document.getElementById("logo");
if (logoLink && typeof isLoggedIn !== "undefined" && isLoggedIn) {
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

  if (avatarBtn && dropdown) {
    avatarBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', function () {
      dropdown.style.display = 'none';
    });
  }

  function openPopup() {
    document.getElementById("popupMenu").style.display = "block";
  }

  function closePopup() {
    document.getElementById("popupMenu").style.display = "none";
  }

  window.addEventListener("click", function (e) {
    const dropdown = document.getElementById("dropdownMenu");
    const avatarBtn = document.getElementById("avatarBtn");

    if (dropdown && avatarBtn && !dropdown.contains(e.target) && !avatarBtn.contains(e.target)) {
      dropdown.style.display = "none";
    }

    const popup = document.getElementById("popupMenu");
    if (popup && e.target === popup) {
      closePopup();
    }
  });
});
