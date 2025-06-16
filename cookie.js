window.addEventListener('DOMContentLoaded', () => {
  if (localStorage.getItem('cookieConsent')) return;
  const banner = document.createElement('div');
  banner.id = 'cookie-banner';
  banner.style.position = 'fixed';
  banner.style.bottom = '0';
  banner.style.left = '0';
  banner.style.right = '0';
  banner.style.background = '#222';
  banner.style.color = '#fff';
  banner.style.padding = '10px';
  banner.style.textAlign = 'center';
  banner.innerHTML = 'Diese Website verwendet Cookies. <button id="acceptCookies">OK</button>';
  document.body.appendChild(banner);
  document.getElementById('acceptCookies').addEventListener('click', () => {
    localStorage.setItem('cookieConsent', '1');
    banner.remove();
  });
});
