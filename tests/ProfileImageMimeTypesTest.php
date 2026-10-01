<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/User_service.php';

class ProfileImageMimeTypesTest extends TestCase {
    public function testAllowedProfileImageExtensionsMapToDetectedImageMimes() {
        $mimes = require dirname(__DIR__) . '/application/config/mimes.php';

        $this->assertContains('image/jpeg', $mimes['jpg']);
        $this->assertContains('image/jpeg', $mimes['jpeg']);
        $this->assertSame('image/png', $mimes['png']);
        $this->assertSame('image/gif', $mimes['gif']);
        $this->assertSame('image/webp', $mimes['webp']);
    }

    public function testOversizedLandscapeAndPortraitImagesAreCroppedToSquare() {
        foreach (array(array(2400, 1200), array(1200, 2400)) as $dimensions) {
            $image_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'profile-image-' . uniqid('', TRUE) . '.jpg';
            $source = imagecreatetruecolor($dimensions[0], $dimensions[1]);
            $color = imagecolorallocate($source, 35, 110, 190);
            imagefill($source, 0, 0, $color);
            imagejpeg($source, $image_path, 90);
            imagedestroy($source);

            $service = (new ReflectionClass(User_service::class))->newInstanceWithoutConstructor();
            $crop_method = new ReflectionMethod(User_service::class, 'crop_profile_image');
            $crop_method->setAccessible(TRUE);

            $this->assertTrue($crop_method->invoke($service, $image_path));
            $image_info = getimagesize($image_path);
            $this->assertSame(512, $image_info[0]);
            $this->assertSame(512, $image_info[1]);

            unlink($image_path);
        }
    }
}
