<?php
namespace Commerce\Core;

use Intervention\Image\ImageManager;
class Image
{
    /**
     * Create Thumnail JustFit
     * @param String $base
     * @param String $destination
     * @param array $size
     */
    static public function createThumbnail($base, $destination, $size = [])
    {
        File::make_dir($destination);
        foreach ($size as $thumb) {
            $crop = false;
            $fill = false;
            if (isset($thumb['crop'])) {
                $crop = $thumb['crop'];
            }
            if (isset($thumb['fill'])) {
                $fill = $thumb['fill'];
            }
            if ($crop) {
                self::createCropedThumnail($base, $destination, $thumb);
            } else if ($fill) {
                self::createFilledThumbail($base, $destination, $thumb);
            } else {
                $manager = new ImageManager(['driver' => 'gd']);
                $image = $manager->make($base);
                $image->orientate();
                $height = $image->height();
                $width = $image->width();
                if ($height > $width) {
                    $image->resize(null, $thumb["height"], function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                } else {
                    $image->resize($thumb["width"], null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
                $image->save($destination, 100);
                $image->destroy();
            }
            chmod($destination, 0777);
        }
        return true;
    }
    /**
     * Create Thumnail Cropped
     * @param String $base
     * @param String $destination
     * @param array $size
     */
    static public function createCropedThumnail($base, $destination, $size = [])
    {
        File::make_dir($destination);

        $manager = new ImageManager(['driver' => 'gd']);
        $image = $manager->make($base);
        $image->orientate();
        $image->fit($size['width'], $size['height']);
        $image->save($destination, 100);
        $image->destroy();
    }

    /**
     * Create Thumnail Filled With Color
     * @param String $base
     * @param String $destination
     * @param Array $size
     */
    static public function createFilledThumbail($base, $destination, $size = array(), $color = "#fff")
    {
        File::make_dir($destination);

        $manager = new ImageManager(['driver' => 'gd']);
        $base_image = $manager->canvas($size["width"], $size["height"], $color);

        $image = $manager->make($base);
        $image->orientate();

        $height = $image->height();
        $width = $image->width();

        if ($height > $width) {
            $image->resize($size["width"], null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        } else {
            $image->resize(null, $size["height"], function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        $base_image->insert($image, 'center');
        $base_image->save($destination, 100);

        $base_image->destroy();
        $image->destroy();
    }
}