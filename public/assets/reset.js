 // Sayfa yüklendiğinde localStorage'dan dark mode'u kontrol et
window.addEventListener('DOMContentLoaded', () => {
  const isDarkMode = localStorage.getItem('darkMode');
  if (isDarkMode === 'enabled') {
    document.body.classList.add('dark-mode');
  }
}); 
// Butona tıklanınca dark mode aç/kapat
document.getElementById('dark-mode-toggle').addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');

  if (document.body.classList.contains('dark-mode')) {
    localStorage.setItem('darkMode', 'enabled'); // aktif halde sakla
  } else {
    localStorage.setItem('darkMode', 'disabled'); // kapalı olarak sakla
  }
}); 


