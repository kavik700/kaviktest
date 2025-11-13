<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

class Install {
    /**
     * Current version of the plugin
     *
     * @var string
     */
    private $current_version;

    /**
     * Constructor
     */
    public function __construct() {
        $this->current_version = MC_CUSTOM_QUIZ_TYPES_VERSION;
        $this->init();
    }

    /**
     * Initialize the installation process
     */
    private function init() {
        $installed_version = get_option('mc_custom_quiz_types_version', '0.0.0');
        
        if (version_compare($installed_version, $this->current_version, '<')) {
            $this->handle_version_updates($installed_version);
            update_option('mc_custom_quiz_types_version', $this->current_version);
        }
    }

    /**
     * Handle version-specific updates
     *
     * @param string $installed_version Currently installed version
     */
    private function handle_version_updates($installed_version) {
        $methods = get_class_methods($this);
        $update_methods = array_filter($methods, function($method) {
            return preg_match('/^update_to_\d+_\d+_\d+$/', $method);
        });

        foreach ($update_methods as $method) {
            $version = $this->get_version_from_method($method);
            if (version_compare($installed_version, $version, '<')) {
                $this->$method();
            }
        }
    }

    /**
     * Extract version number from method name
     *
     * @param string $method Method name
     * @return string Version number
     */
    private function get_version_from_method($method) {
        preg_match('/update_to_(\d+_\d+_\d+)$/', $method, $matches);
        return str_replace('_', '.', $matches[1]);
    }

    /**
     * Update to version 0.24.0
     */
    private function update_to_0_25_0() {
        global $wpdb;
        
        // Create smart crop table if it doesn't exist
        $smart_crop_table = $wpdb->prefix . 'smart_crop';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $smart_crop_table (
            id int NOT NULL AUTO_INCREMENT,
            source_url varchar(1024) NOT NULL,
            target_url varchar(1024) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
} 