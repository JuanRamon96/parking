var sidebar = document.getElementById('sidebar');
var mainContent = document.getElementById('mainContent');
var sbToggle = document.getElementById('sbToggle');
var sbToggleIcon = document.getElementById('sbToggleIcon');
var sbCloseMobile = document.getElementById('sbCloseMobile');
var mobileToggle = document.getElementById('mobileToggle');
var backdrop = document.getElementById('sbBackdrop');
var highlight = document.getElementById('sbHighlight');
var items = Array.from(document.querySelectorAll('.sb-item.cargarVista'));

var isCollapsed = false;   // Estado "real" elegido con el botón (persistente)
var isHovering = false;    // ¿El mouse está físicamente sobre el sidebar AHORA?
var canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

function moveHighlight(el) {
    if (!el) { highlight.style.opacity = 0; return; }
    highlight.style.opacity = 1;
    highlight.style.transform = `translateY(${(el.offsetTop - 8)}px)`;
    highlight.style.height = el.offsetHeight + 'px';
}

// Actualiza el ícono del botón toggle según el estado lógico (pin / unpin)
function updateToggleIcon() {
    if (!sbToggleIcon) return;
    sbToggleIcon.classList.toggle('unpinned', isCollapsed);
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
    updateToggleIcon();
    moveHighlight(document.querySelector('.sb-item.active'));
}
window.addEventListener('load', () => setTimeout(setInitialState, 60));

// Botón para colapsar / expandir el menú (afecta el estado LÓGICO, no directamente la clase visual)
sbToggle.addEventListener('click', () => {
    isCollapsed = !isCollapsed;
    mainContent.classList.toggle('collapsed', isCollapsed);
    syncSidebarVisual();
    updateToggleIcon();
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

document.querySelectorAll('.sb-parent').forEach(parent => {
    parent.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const group = parent.closest('.sb-group');
        group?.classList.toggle('open');
        setTimeout(() => moveHighlight(document.querySelector('.sb-item.active')), 180);
    });
});

items.forEach(item => {
    item.addEventListener('click', (e) => {
        e.preventDefault();
        document.querySelector('.sb-item.active')?.classList.remove('active');
        item.classList.add('active');
        const group = item.closest('.sb-group');
        if (group) {
            group.classList.add('open');
        }
        moveHighlight(item);
        document.querySelector('.tb-title').textContent = item.querySelector('.label').textContent;
        const crumb = item.getAttribute('crumb');
        if (crumb && document.querySelector('.tb-crumb')) {
            document.querySelector('.tb-crumb').textContent = crumb;
        }
        if (window.innerWidth < 992) closeMobile();
    });
});

// Redimensionamiento de ventana
var resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        if (window.innerWidth >= 992) closeMobile();
        syncSidebarVisual();
    }, 120);
});