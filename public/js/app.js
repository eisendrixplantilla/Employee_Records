// Sidebar toggle (mobile)
document.addEventListener('click', (e) => {
  const toggle = e.target.closest('[data-sidebar-toggle]');
  const closeEl = e.target.closest('[data-sidebar-close]');
  const sidebar = document.querySelector('.ers-sidebar');
  const backdrop = document.querySelector('.sidebar-backdrop');
  if (toggle && sidebar) {
    sidebar.classList.toggle('show');
    backdrop && backdrop.classList.toggle('show');
  }
  if (closeEl && sidebar) {
    sidebar.classList.remove('show');
    backdrop && backdrop.classList.remove('show');
  }
});

// Active menu highlight based on current file name
(function highlightActive(){
  const path = (location.pathname.split('/').pop() || 'index.html').toLowerCase();
  document.querySelectorAll('.ers-sidebar .nav-link').forEach(link => {
    const target = (link.getAttribute('data-page') || '').toLowerCase();
    if (target && path.includes(target)) link.classList.add('active');
  });
})();

// SweetAlert2 toast helper
window.ersToast = function(icon, title){
  if (!window.Swal) return;
  Swal.mixin({
    toast:true, position:'top-end', showConfirmButton:false,
    timer:2800, timerProgressBar:true
  }).fire({ icon, title });
};

// Confirm delete helper
window.ersConfirmDelete = function(message, onConfirm){
  if (!window.Swal) { if(confirm(message)) onConfirm && onConfirm(); return; }
  Swal.fire({
    title:'Are you sure?',
    text: message || 'This action cannot be undone.',
    icon:'warning',
    showCancelButton:true,
    confirmButtonColor:'#ef4444',
    cancelButtonColor:'#6b7280',
    confirmButtonText:'Yes, delete it'
  }).then(r => {
    if (r.isConfirmed) {
      onConfirm && onConfirm();
      ersToast('success','Deleted successfully');
    }
  });
};

// Image preview
window.ersPreviewImage = function(input, targetId){
  const file = input.files && input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.getElementById(targetId);
    if (img) img.src = e.target.result;
  };
  reader.readAsDataURL(file);
};
