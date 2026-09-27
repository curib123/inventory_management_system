const assert = require('assert');
const fs = require('fs');
const path = require('path');

const root = path.join('application', 'views', 'modal');

function phpFiles(directory) {
    return fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
        const entryPath = path.join(directory, entry.name);

        if (entry.isDirectory()) {
            return phpFiles(entryPath);
        }

        return entry.name.endsWith('.php') ? [entryPath] : [];
    });
}

phpFiles(root)
    .filter((file) => path.basename(file) !== 'container.php')
    .forEach((file) => {
        const template = fs.readFileSync(file, 'utf8');

        assert.match(template, /app-modal-header|components\/modal\/header/);
        assert.match(template, /class="app-modal-body"/);
        assert.match(template, /app-modal-footer|components\/modal\/footer/);
        assert.doesNotMatch(template, /class="modal-(header|body|footer|dialog|content)/);
    });

console.log('PASS modal template audit');
