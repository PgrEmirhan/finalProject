window.addEventListener('DOMContentLoaded', () => {
  const isDarkMode = localStorage.getItem('darkMode');
  if (isDarkMode === 'enabled') {
    document.body.classList.add('dark-mode');
  }
  updateLogo();

});   
  function updateLogo() {
    const logo = document.getElementById('logo');
    const isDark = document.body.classList.contains('dark-mode');
    if (logo) {
      logo.src = isDark ? 'images/logo-1.png' : 'images/logo.png';
    }
  }

document.getElementById('dark-mode-toggle').addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');
    updateLogo();

  if (document.body.classList.contains('dark-mode')) {
    localStorage.setItem('darkMode', 'enabled');
  } else {
    localStorage.setItem('darkMode', 'disabled'); 
  }
}); 


