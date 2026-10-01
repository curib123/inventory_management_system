const assert = require('assert');
const fs = require('fs');
const vm = require('vm');

// Execute the shared page script with image load states supplied by the browser.
function avatar(complete, naturalWidth) {
    const listeners = {};
    const initials = { hidden: false };
    return {
        complete, naturalWidth, hidden: true, initials,
        parentElement: { querySelector: () => initials },
        addEventListener: (event, listener) => { listeners[event] = listener; },
        dispatch: event => listeners[event]()
    };
}

const cached = avatar(true, 512);
const failed = avatar(true, 0);
const pending = avatar(false, 0);
const images = [cached, failed, pending];
let ready;
const document = {
    getElementById: () => null,
    querySelector: () => null,
    querySelectorAll: selector => selector === '[data-avatar-image]' ? images : [],
    addEventListener: (event, listener) => { if (event === 'DOMContentLoaded') ready = listener; }
};
vm.runInNewContext(fs.readFileSync('assets/js/app.js', 'utf8'), { document, window: {} });
ready();

assert.equal(cached.hidden, false, 'A cached profile photo should be visible');
assert.equal(cached.initials.hidden, true);
assert.equal(failed.hidden, true, 'A broken photo should stay hidden');
assert.equal(failed.initials.hidden, false);
assert.equal(pending.hidden, true, 'Show initials until the photo has loaded');
pending.naturalWidth = 512;
pending.dispatch('load');
assert.equal(pending.hidden, false);
assert.equal(pending.initials.hidden, true);
pending.dispatch('error');
assert.equal(pending.hidden, true, 'Restore initials when a photo request fails');
assert.equal(pending.initials.hidden, false);

console.log('PASS avatar cached, loading, and failed image states');
