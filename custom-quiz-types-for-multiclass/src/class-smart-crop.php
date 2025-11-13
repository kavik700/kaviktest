<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

use Exception;

class Smart_Crop {
    public static function crop_to_largest_component($image_url, $alpha_threshold = 100) {
        $image_content = file_get_contents($image_url);
        if ($image_content === false) throw new Exception("Failed to load image from URL");
        $image = imagecreatefromstring($image_content);
        if ($image === false) throw new Exception("Failed to create image from string");

        $width = imagesx($image);
        $height = imagesy($image);
        $visited = array_fill(0, $height, array_fill(0, $width, false));
        $components = [];

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($visited[$y][$x]) continue;
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;
                if ($alpha < $alpha_threshold) {
                    // Start BFS
                    $queue = [[$x, $y]];
                    $component = [];
                    $min_x = $max_x = $x;
                    $min_y = $max_y = $y;
                    while ($queue) {
                        list($cx, $cy) = array_pop($queue);
                        if ($cx < 0 || $cy < 0 || $cx >= $width || $cy >= $height) continue;
                        if ($visited[$cy][$cx]) continue;
                        $visited[$cy][$cx] = true;
                        $rgba2 = imagecolorat($image, $cx, $cy);
                        $alpha2 = ($rgba2 & 0x7F000000) >> 24;
                        if ($alpha2 < $alpha_threshold) {
                            $component[] = [$cx, $cy];
                            if ($cx < $min_x) $min_x = $cx;
                            if ($cx > $max_x) $max_x = $cx;
                            if ($cy < $min_y) $min_y = $cy;
                            if ($cy > $max_y) $max_y = $cy;
                            // 4-connectivity
                            $queue[] = [$cx+1, $cy];
                            $queue[] = [$cx-1, $cy];
                            $queue[] = [$cx, $cy+1];
                            $queue[] = [$cx, $cy-1];
                        }
                    }
                    if (count($component) > 0) {
                        $components[] = [
                            'pixels' => $component,
                            'minX' => $min_x, 'maxX' => $max_x,
                            'minY' => $min_y, 'maxY' => $max_y,
                            'size' => count($component)
                        ];
                    }
                } else {
                    $visited[$y][$x] = true;
                }
            }
        }

        // Find largest component
        if (empty($components)) {
            ob_start(); imagepng($image); $data = ob_get_clean();
            imagedestroy($image); return $data;
        }
        usort($components, fn($a, $b) => $b['size'] - $a['size']);
        $main = $components[0];
        $left = $main['minX'];
        $right = $main['maxX'];
        $top = $main['minY'];
        $bottom = $main['maxY'];

        $new_width = $right - $left + 1;
        $new_height = $bottom - $top + 1;
        $cropped = imagecreatetruecolor($new_width, $new_height);
        imagesavealpha($cropped, true);
        $trans = imagecolorallocatealpha($cropped, 0, 0, 0, 127);
        imagefill($cropped, 0, 0, $trans);
        imagecopy($cropped, $image, 0, 0, $left, $top, $new_width, $new_height);

        ob_start(); imagepng($cropped); $data = ob_get_clean();
        imagedestroy($image); imagedestroy($cropped);
        return $data;
    }

    private static function save_url_mapping($source_url, $target_url) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'smart_crop';
        $wpdb->insert(
            $table_name,
            array(
                'source_url' => $source_url,
                'target_url' => $target_url
            ),
            array('%s', '%s')
        );
    }

    private static function get_cached_url($source_url) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'smart_crop';
        return $wpdb->get_var($wpdb->prepare(
            "SELECT target_url FROM $table_name WHERE source_url = %s",
            $source_url
        ));
    }

    // Helper to normalize URLs
    private static function normalize_url($url) {
        // If URL already has http or https protocol, return as is
        if (preg_match('/^https?:\/\//', $url)) {
            return $url;
        }
        // Otherwise, prepend https://
        return 'https://' . ltrim($url, '/');
    }

    public static function cached_crop_to_largest_component($image_url, $alpha_threshold = 100) {
        // Check if the image is already in the database
        $existing = self::get_cached_url($image_url);
        if ($existing) {
            return self::normalize_url($existing);
        }

        $ext = pathinfo($image_url, PATHINFO_EXTENSION);
        if (strtolower($ext) !== 'png') {
            return $image_url;
        }

        try {
            $cropped_image_data = self::crop_to_largest_component($image_url);
            
            // Create a temporary file to store the cropped image
            $upload_dir = wp_upload_dir();
            $temp_file = tempnam($upload_dir['path'], 'smart_crop_');
            file_put_contents($temp_file, $cropped_image_data);
            
            // Get the file extension from the original URL
            $ext = pathinfo($image_url, PATHINFO_EXTENSION);
            if (empty($ext)) {
                $ext = 'png'; // Default to PNG if no extension found
            }
            
            // Create a new filename
            $new_filename = 'smart_crop_' . md5($image_url) . '.' . $ext;
            $new_filepath = $upload_dir['path'] . '/' . $new_filename;
            
            // Move the temporary file to the uploads directory
            rename($temp_file, $new_filepath);
            
            // Set file permissions to 644
            chmod($new_filepath, 0644);
            
            // Get the target URL
            $target_url = $upload_dir['url'] . '/' . $new_filename;
            
            // Save the URL mapping
            self::save_url_mapping($image_url, $target_url);
            
            // Ensure the URL has the correct protocol and domain
            return self::normalize_url($target_url);
        } catch (Exception $e) {
            // If anything goes wrong, return the original URL
            return $image_url;
        }
    }
}