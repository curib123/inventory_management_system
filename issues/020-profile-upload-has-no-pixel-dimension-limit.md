# Issue 020 — Small compressed avatar files can require excessive image memory

**Severity:** Medium  
**Status:** Open; proposed fix only  
**Reviewed commit:** `9c7229442fa48a8edd66898d970ace71e9bea17a`

## Evidence and impact

`application/libraries/User_service.php::upload_profile_image()` caps file size at 2 MB, but has no dimension limit. `crop_profile_image()` calls `getimagesize()` and then decodes the full source with GD without checking width, height, or pixel count.

In an isolated reproduction, a solid PNG with dimensions **1024 × 9000** occupied only **27,011 bytes** and was accepted by the current crop method. Its decoded source needs far more memory than its compressed file size. Larger inputs can exhaust worker resources; whether a particular input fails depends on the server's available memory. A 512px output does not limit source decoding costs.

The existing landscape/portrait crop test exercises 2400 × 1200 inputs and does not cover a source limit. None of guides 001–016 covers avatar dimensions.

## Fix with code

In `crop_profile_image()`, add this immediately after the existing MIME check and **before** `imagecreatefromstring()`:

```php
$width = (int) $image_info[0];
$height = (int) $image_info[1];
$maximum_dimension = 4096;
$maximum_pixels = 8000000;

if (
    $width < 1 ||
    $height < 1 ||
    $width > $maximum_dimension ||
    $height > $maximum_dimension ||
    $width * $height > $maximum_pixels
) {
    return FALSE;
}
```

These are proposed conservative application limits, not a guarantee for every hosting memory budget. Tune them downward if needed. Apply the check inside the crop method so every caller is protected. Keep the existing MIME and 2 MB checks.

The current upload failure branch already deletes the rejected file. Make its message actionable:

```php
return array(
    'success' => FALSE,
    'message' => 'Choose a valid image up to 4096 pixels per side and 8 million pixels, then try again.'
);
```

Use that response in the existing crop-failure branch, keeping its `delete_profile_image($filename)` call. Update the upload field's help text in `application/views/modal/users/form.php` to mention the dimension limits as well as the 2 MB limit.

## Verify

```bash
php -l application/libraries/User_service.php
vendor/bin/phpunit --filter ProfileImageMimeTypesTest
```

Add crop tests for 4097 × 64, 1024 × 9000, and 3000 × 3000 sources: reject all three before decoding. Preserve acceptance and square output for the existing 2400 × 1200 and 1200 × 2400 fixtures. For an actual rejected upload, confirm the previous avatar and database value remain intact and the rejected file is removed.

Do not test rejection by deliberately exhausting a production worker. Small generated fixtures can demonstrate the bounds safely.

**Validation performed during analysis:** the current method accepted the 1024 × 9000 fixture; the proposed dimension check rejected it in a temporary copy. Existing accepted-image tests still passed there.
