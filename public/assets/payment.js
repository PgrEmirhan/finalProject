window.addEventListener('DOMContentLoaded', () => {
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
});

  const avatarBtn = document.getElementById('avatarBtn');
  const dropdown = document.getElementById('dropdownMenu');

  avatarBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
  });

  document.addEventListener('click', function () {
    dropdown.style.display = 'none';
  });

  function openPopup() {
    document.getElementById("popupMenu").style.display = "block";
  }

  function closePopup() {
    document.getElementById("popupMenu").style.display = "none";
  }

  window.addEventListener("click", function (e) {
    const dropdown = document.getElementById("dropdownMenu");
    const avatarBtn = document.getElementById("avatarBtn");
        const popup = document.getElementById("popupMenu");

    if (!dropdown.contains(e.target) && !avatarBtn.contains(e.target)  && !popup.contains(e.target)) {
      dropdown.style.display = "none";

    } 
  });

  function updateCard(input, fieldType) {
    const value = input.value.trim();

    const fields = {
      'card-number': formatCardNumber(value),
      'name': value || 'Ad Soyad',
      'expiry': formatExpiry(value)
    };

    const ids = {
      'card-number': ['show-card-number', 'show-card-number-mastercard', 'show-card-number-troy'],
      'name': ['show-name', 'show-name-mastercard', 'show-name-troy'],
      'expiry': ['show-expiry', 'show-expiry-mastercard', 'show-expiry-troy']
    };

    ids[fieldType].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.innerText = fields[fieldType];
    });

    if (fieldType === 'card-number') input.value = formatCardNumber(value);
    if (fieldType === 'expiry') input.value = formatExpiry(value);
  }

  function formatCardNumber(val) {
    return val.replace(/\D/g, '').slice(0, 16).replace(/(.{4})/g, '$1 ').trim() || '**** **** **** ****';
  }

  function formatExpiry(val) {
    let cleaned = val.replace(/\D/g, '').slice(0, 4);
    if (cleaned.length > 2) cleaned = cleaned.slice(0, 2) + '/' + cleaned.slice(2);
    return cleaned || 'AA/YY';
  }

  function selectCard(selectedCard, type) {
    const cards = document.querySelectorAll('.card-container');
    const angles = ['rotate(160deg)', 'rotate(380deg)'];
    let angleIndex = 0;

    cards.forEach(card => {
      card.classList.remove('selected');

      if (card === selectedCard) {
        card.style.transform = 'translateY(-20px) scale(1.05)';
      } else {
        card.style.transform = angles[angleIndex];
        angleIndex++;
      }
    });

    selectedCard.classList.add('selected');
    document.getElementById('card-type').value = type;
  }

  document.addEventListener('DOMContentLoaded', function () {
    const selectedCard = document.querySelector('.card-container.selected');
    const cards = document.querySelectorAll('.card-container');
    let angleOptions = ['rotate(160deg)', 'rotate(380deg)'];
    let angleIndex = 0;

    cards.forEach(card => {
      if (card === selectedCard) {
        card.style.transform = 'translateY(-20px) scale(1.05)';
      } else {
        card.style.transform = angleOptions[angleIndex];
        angleIndex++;
      }
    });
  });
  function validateName(input) {
  input.value = input.value.replace(/[^a-zA-ZğüşöçİĞÜŞÖÇ\s]/g, '');
}
function validateCVV(input) {
  input.value = input.value.replace(/\D/g, '');
}
