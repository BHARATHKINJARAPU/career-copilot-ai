const showToast = (message, type = 'info') => {
  const toast = document.createElement('div');
  toast.className = 'glass-card';
  toast.style.position = 'fixed';
  toast.style.bottom = '20px';
  toast.style.right = '20px';
  toast.style.padding = '12px 24px';
  toast.style.zIndex = '9999';
  toast.style.borderLeft = type === 'success' ? '4px solid #10b981' : '4px solid #38bdf8';
  toast.style.color = '#fff';
  toast.innerText = message;
  
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.5s ease';
    setTimeout(() => toast.remove(), 500);
  }, 3500);
};

window.showToast = showToast;