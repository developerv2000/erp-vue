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
