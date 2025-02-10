// Selección de elementos
const menuToggle = document.getElementById('menu-toggle');
const navMenu = document.getElementById('nav-menu');

// Evento de clic para abrir/cerrar el menú
menuToggle.addEventListener('click', () => {
  navMenu.classList.toggle('active'); // Alterna entre mostrar/ocultar
});
