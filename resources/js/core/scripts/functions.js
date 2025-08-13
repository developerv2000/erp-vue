/**
 * Handles global click delegation for specific `data-on-click` actions.
 * Currently supports: toggle-text-max-lines
 */
export function handleGlobalClickDelegates(event) {
    const el = event.target.closest('[data-on-click="toggle-text-max-lines"]');
    if (!el) return;

    el.classList.toggle('max-lines-limited-text');
    event.stopPropagation();
}

export function enterFullscreen(target) {
    if (target.requestFullscreen) {
        target.requestFullscreen();
    } else if (target.webkitRequestFullscreen) {
        target.webkitRequestFullscreen();
    } else if (target.msRequestFullscreen) {
        target.msRequestFullscreen();
    }
};

export function toggleFullscreenClass(target) {
    if (document.fullscreenElement) {
        target.classList.add('fullscreen');
    } else {
        target.classList.remove('fullscreen');
    }
};
