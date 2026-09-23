<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo html_escape($report_title); ?></title>
</head>
<body>
    <h1><?php echo html_escape($report_title); ?></h1>
    <p>Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
    <table>
        <thead>
            <tr>
                <?php if (!empty($rows)): foreach (array_keys($rows[0]) as $header): ?>
                    <th><?php echo html_escape(ucwords(str_replace('_', ' ', $header))); ?></th>
                <?php endforeach; endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rows)): foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($row as $value): ?>
                        <td><?php echo html_escape($value); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; else: ?>
                <tr><td>No report data found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
