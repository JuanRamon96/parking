const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const sbToggle = document.getElementById('sbToggle');
const sbCloseMobile = document.getElementById('sbCloseMobile');
const mobileToggle = document.getElementById('mobileToggle');
const backdrop = document.getElementById('sbBackdrop');
const highlight = document.getElementById('sbHighlight');
const items = Array.from(document.querySelectorAll('.sb-item'));

let isCollapsed = false;   // Estado "real" elegido con el botón (persistente)
let isHovering = false;    // ¿El mouse está físicamente sobre el sidebar AHORA?
const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

function moveHighlight(el) {
    if (!el) { highlight.style.opacity = 0; return; }
    highlight.style.opacity = 1;
    highlight.style.transform = `translateY(${(el.offsetTop - 8)}px)`;
    highlight.style.height = el.offsetHeight + 'px';
}

// Sincroniza la clase visual "collapsed" según el estado lógico + si hay hover activo.
// Así, sin importar si el mouse ya estaba encima al hacer clic, el resultado visual
// siempre respeta: colapsado lógicamente + NO hover = chico; colapsado + hover = expandido.
function syncSidebarVisual() {
    const shouldLookExpanded = !isCollapsed || (canHover && isHovering && window.innerWidth >= 992);
    sidebar.classList.toggle('collapsed', !shouldLookExpanded);
    setTimeout(() => moveHighlight(document.querySelector('.sb-item.active')), 200);
}

function setInitialState() {
    if (window.innerWidth >= 992 && window.innerWidth < 1280) {
        isCollapsed = true;
        mainContent.classList.add('collapsed');
    }
    syncSidebarVisual();
    moveHighlight(document.querySelector('.sb-item.active'));
}
window.addEventListener('load', () => setTimeout(setInitialState, 60));

// Botón para colapsar / expandir el menú (afecta el estado LÓGICO, no directamente la clase visual)
sbToggle.addEventListener('click', () => {
    isCollapsed = !isCollapsed;
    mainContent.classList.toggle('collapsed', isCollapsed);
    syncSidebarVisual();
});

// Hover: solo actualiza el flag; syncSidebarVisual decide qué se ve
sidebar.addEventListener('mouseenter', () => {
    isHovering = true;
    if (canHover && window.innerWidth >= 992) syncSidebarVisual();
});
sidebar.addEventListener('mouseleave', () => {
    isHovering = false;
    if (canHover && window.innerWidth >= 992) syncSidebarVisual();
});

// Funciones para vista móvil
function openMobile() { sidebar.classList.add('mobile-open'); backdrop.classList.add('show'); document.body.style.overflow = 'hidden'; }
function closeMobile() { sidebar.classList.remove('mobile-open'); backdrop.classList.remove('show'); document.body.style.overflow = ''; }
mobileToggle.addEventListener('click', openMobile);
sbCloseMobile.addEventListener('click', closeMobile);
backdrop.addEventListener('click', closeMobile);
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && sidebar.classList.contains('mobile-open')) closeMobile();
});

// Navegación de items
items.forEach(item => {
    item.addEventListener('click', (e) => {
        e.preventDefault();
        document.querySelector('.sb-item.active')?.classList.remove('active');
        item.classList.add('active');
        moveHighlight(item);
        document.querySelector('.tb-title').textContent = item.querySelector('.label').textContent;
        if (window.innerWidth < 992) closeMobile();
    });
});

// Redimensionamiento de ventana
let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        if (window.innerWidth >= 992) closeMobile();
        syncSidebarVisual();
    }, 120);
});