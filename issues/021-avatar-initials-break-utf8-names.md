# Issue 021 — Byte-based initials corrupt accented and non-Latin names

**Severity:** Low  
**Status:** Open; proposed fix only  
**Reviewed commit:** `9c7229442fa48a8edd66898d970ace71e9bea17a`

## Evidence and reproduction

`application/views/templates/header.php` calculates initials using `strtoupper(substr($part, 0, 1))`. `substr()` takes one byte, not one UTF-8 character.

For `Élodie Niño`, the current result is invalid UTF-8: the first byte of `É` is incomplete. With the current escaping path this can display incorrectly or leave initials blank. This affects both navigation avatars when no usable photo exists. It is distinct from missing-image handling and from the guides 001–016.

## Fix with code

Replace the block starting at `$current_name_parts = ...` and ending at the fallback `U` assignment with:

```php
$current_name_parts = preg_split(
    '/\s+/u',
    $current_display_name,
    -1,
    PREG_SPLIT_NO_EMPTY
);
$current_avatar_initials = '';

if (!empty($current_name_parts)) {
    $current_avatar_initials = mb_strtoupper(
        mb_substr($current_name_parts[0], 0, 1, 'UTF-8'),
        'UTF-8'
    );

    if (count($current_name_parts) > 1) {
        $current_avatar_initials .= mb_strtoupper(
            mb_substr($current_name_parts[count($current_name_parts) - 1], 0, 1, 'UTF-8'),
            'UTF-8'
        );
    }
}

if ($current_avatar_initials === '') {
    $current_avatar_initials = 'U';
}
```

This requires PHP's `mbstring` extension. Check `php -m` and enable it for both the CLI and web-server PHP configuration. Keep `html_escape()` in the avatar markup.

## Verify

```bash
php -l application/views/templates/header.php
node tests/avatar_image_test.js
```

Test accounts without a photo:

| Display name | Expected initials |
| --- | --- |
| Curib Tech | CT |
| Élodie Niño | ÉN |
| Álvaro Ñúñez | ÁÑ |
| 山田 太郎 | 山太 |
| Élodie | É |

Check the top navigation and sidebar. The result must remain valid UTF-8 and the default `User` fallback should still give `U`. The JavaScript image-state test alone does not check server-rendered Unicode initials; add a PHP rendering test for these examples.

**Validation performed during analysis:** reproduced invalid UTF-8 with the existing expression and verified the replacement expression against the table above.
