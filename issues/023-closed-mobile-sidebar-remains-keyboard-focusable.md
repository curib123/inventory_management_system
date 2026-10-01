# Issue 023 — Keyboard focus enters the closed mobile sidebar

**Severity:** Medium  
**Status:** Open; proposed fix only  
**Reviewed commit:** `9c7229442fa48a8edd66898d970ace71e9bea17a`

## Evidence and reproduction

At widths below 992px, `assets/css/sidebar.css` hides the sidebar with `transform: translateX(-100%)`. Its links remain in the document's tab order. `assets/js/app.js::setSidebarOpen()` changes CSS classes and the toggle's `aria-expanded`, but does not disable focus in the hidden navigation.

At a 375px viewport, the first Tab from the page body focused the hidden Dashboard link at an x-coordinate around **-280px**. A keyboard user loses the visible focus indicator. The merged avatar fix does not address this separate interaction issue, and none of guides 001–016 covers it.

## Fix with code

In `assets/js/app.js`, replace the existing sidebar function, toggle/backdrop listeners, and its Escape listener with the block below. Keep the earlier avatar code and all modal handlers unchanged.

```javascript
var sidebarMedia = sidebar
    ? window.matchMedia('(max-width: 991.98px)')
    : null;

function setSidebarOpen(open) {
    if (!sidebar) {
        return;
    }

    var mobile = sidebarMedia.matches;
    var visible = mobile && Boolean(open);
    var focusWasInside = sidebar.contains(document.activeElement);
    var wasOpen = sidebar.classList.contains('show');

    sidebar.classList.toggle('show', visible);
    sidebar.inert = mobile && !visible;

    if (sidebarBackdrop) {
        sidebarBackdrop.classList.toggle('show', visible);
    }

    if (sidebarToggle) {
        sidebarToggle.setAttribute('aria-expanded', visible ? 'true' : 'false');
        sidebarToggle.setAttribute('aria-label', visible ? 'Close navigation' : 'Open navigation');
    }

    if (visible) {
        var firstLink = sidebar.querySelector('a[href]');
        if (firstLink) {
            firstLink.focus({ preventScroll: true });
        }
    } else if (mobile && (focusWasInside || wasOpen) && sidebarToggle) {
        sidebarToggle.focus({ preventScroll: true });
    }
}

if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
        setSidebarOpen(!sidebar.classList.contains('show'));
    });
}

if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', function () {
        setSidebarOpen(false);
    });
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && sidebar && sidebar.classList.contains('show')) {
        setSidebarOpen(false);
    }
});

if (sidebarMedia) {
    sidebarMedia.addEventListener('change', function () {
        setSidebarOpen(false);
    });
    setSidebarOpen(false);
}
```

`inert` removes the closed drawer's descendants from focus and interaction while retaining normal desktop navigation. Handle the media query change so a mobile `inert` state cannot carry into desktop. The code also restores focus after closing and clears obsolete drawer/backdrop classes when resizing. This fix addresses closed-drawer focus; it does not implement a full focus trap for an open drawer.

## Verify

```bash
node --check assets/js/app.js
node tests/sidebar_ui_test.js
node tests/avatar_image_test.js
```

At 375px, tab through the closed page: no hidden sidebar link should receive focus. Open navigation, check that focus moves to Dashboard, then press Escape: focus should return to the visible toggle. Resize mobile → desktop → mobile and verify desktop links stay focusable, the mobile drawer closes, and its backdrop does not linger. Click the backdrop and repeat the focus-return check.

**Validation performed during analysis:** reproduced offscreen focus in Chromium using the actual header and shared JavaScript; verified the proposed handler in a separate temporary copy, including mobile/desktop resizing and Escape behavior.
