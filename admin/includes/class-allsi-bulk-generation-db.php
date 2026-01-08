<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Bulk Generation Database Class
 * 
 * Handles database table creation and CRUD operations for bulk generation jobs.
 *
 * @package All_Sources_Images
 * @since 6.1.7
 */

// This file manages custom plugin tables, so $wpdb direct queries and schema changes are expected here.
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ALLSI_Bulk_Generation_DB {

    /**
     * Database version
     *
     * @var string
     */
    private static $db_version = '1.1.0';

    /**
     * Table name for jobs (parent)
     *
     * @var string
     */
    public static $table_jobs;

    /**
     * Table name for posts (child)
     *
     * @var string
     */
    public static $table_posts;

    /**
     * Initialize table names
     */
    public static function init() {
        global $wpdb;
        self::$table_jobs  = $wpdb->prefix . 'ALLSI_bulk_jobs';
        self::$table_posts = $wpdb->prefix . 'ALLSI_bulk_posts';
    }

    /**
     * Create database tables on plugin activation
     */
    public static function create_tables() {
        global $wpdb;
        
        self::init();
        
        $charset_collate = $wpdb->get_charset_collate();

        // Parent table: Jobs
        $sql_jobs = "CREATE TABLE IF NOT EXISTS " . self::$table_jobs . " (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            job_name VARCHAR(255) NOT NULL,
            job_status ENUM('pending', 'processing', 'paused', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
            total_posts INT(11) UNSIGNED DEFAULT 0,
            processed_posts INT(11) UNSIGNED DEFAULT 0,
            successful_posts INT(11) UNSIGNED DEFAULT 0,
            failed_posts INT(11) UNSIGNED DEFAULT 0,
            images_per_post INT(11) UNSIGNED DEFAULT 1,
            selection_mode VARCHAR(50) DEFAULT 'custom',
            post_types TEXT,
            settings TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            started_at DATETIME DEFAULT NULL,
            completed_at DATETIME DEFAULT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by BIGINT(20) UNSIGNED DEFAULT NULL,
            PRIMARY KEY (id),
            KEY job_status (job_status),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Child table: Posts
        $sql_posts = "CREATE TABLE IF NOT EXISTS " . self::$table_posts . " (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            job_id BIGINT(20) UNSIGNED NOT NULL,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            post_type VARCHAR(50) NOT NULL,
            post_title VARCHAR(255) NOT NULL,
            status ENUM('pending', 'processing', 'completed', 'failed', 'skipped') DEFAULT 'pending',
            retry_count TINYINT(3) UNSIGNED DEFAULT 0,
            featured_image_id BIGINT(20) UNSIGNED DEFAULT NULL,
            featured_image_status ENUM('pending', 'completed', 'failed', 'skipped') DEFAULT 'pending',
            additional_images TEXT,
            error_message TEXT,
            search_keyword VARCHAR(255) DEFAULT NULL,
            image_source VARCHAR(100) DEFAULT NULL,
            processed_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY job_id (job_id),
            KEY post_id (post_id),
            KEY status (status),
            FOREIGN KEY (job_id) REFERENCES " . self::$table_jobs . "(id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql_jobs );
        dbDelta( $sql_posts );

        // Add retry_count column if it doesn't exist (for upgrades from 1.0.0)
        self::maybe_add_retry_column();

        update_option( 'ALLSI_bulk_db_version', self::$db_version );
    }

    /**
     * Add retry_count column if upgrading from older version
     */
    private static function maybe_add_retry_column() {
        global $wpdb;
        
        self::init();
        
        // Check if column exists
        $column_exists = $wpdb->get_results( $wpdb->prepare(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'retry_count'",
            DB_NAME,
            self::$table_posts
        ) );
        
        if ( empty( $column_exists ) ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is a safe internal constant, not user input.
            $wpdb->query( "ALTER TABLE `" . esc_sql( self::$table_posts ) . "` ADD COLUMN retry_count TINYINT(3) UNSIGNED DEFAULT 0 AFTER status" );
        }
    }

    /**
     * Check if tables exist and create them if not
     */
    public static function maybe_create_tables() {
        $installed_version = get_option( 'ALLSI_bulk_db_version' );
        
        if ( $installed_version !== self::$db_version ) {
            self::create_tables();
        }
    }

    /**
     * Drop tables (for uninstall)
     */
    public static function drop_tables() {
        global $wpdb;
        
        self::init();
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names are safe internal constants.
        $wpdb->query( "DROP TABLE IF EXISTS `" . esc_sql( self::$table_posts ) . "`" );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names are safe internal constants.
        $wpdb->query( "DROP TABLE IF EXISTS `" . esc_sql( self::$table_jobs ) . "`" );
        
        delete_option( 'ALLSI_bulk_db_version' );
    }

    // =====================
    // JOBS CRUD Operations
    // =====================

    /**
     * Create a new bulk generation job
     *
     * @param array $data Job data
     * @return int|false Job ID or false on failure
     */
    public static function create_job( $data ) {
        global $wpdb;
        
        self::init();

        $defaults = array(
            /* translators: %s: Date and time when the job was created. */
            'job_name'        => sprintf( __( 'Bulk Job %s', 'all-sources-images' ), current_time( 'Y-m-d H:i' ) ),
            'job_status'      => 'pending',
            'total_posts'     => 0,
            'processed_posts' => 0,
            'successful_posts'=> 0,
            'failed_posts'    => 0,
            'images_per_post' => 1,
            'selection_mode'  => 'custom',
            'post_types'      => '',
            'settings'        => '',
            'created_by'      => get_current_user_id(),
        );

        $data = wp_parse_args( $data, $defaults );

        // Serialize arrays
        if ( is_array( $data['post_types'] ) ) {
            $data['post_types'] = maybe_serialize( $data['post_types'] );
        }
        if ( is_array( $data['settings'] ) ) {
            $data['settings'] = maybe_serialize( $data['settings'] );
        }

        $result = $wpdb->insert(
            self::$table_jobs,
            array(
                'job_name'        => sanitize_text_field( $data['job_name'] ),
                'job_status'      => $data['job_status'],
                'total_posts'     => absint( $data['total_posts'] ),
                'processed_posts' => absint( $data['processed_posts'] ),
                'successful_posts'=> absint( $data['successful_posts'] ),
                'failed_posts'    => absint( $data['failed_posts'] ),
                'images_per_post' => absint( $data['images_per_post'] ),
                'selection_mode'  => sanitize_text_field( $data['selection_mode'] ),
                'post_types'      => $data['post_types'],
                'settings'        => $data['settings'],
                'created_by'      => absint( $data['created_by'] ),
            ),
            array( '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d' )
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get a job by ID
     *
     * @param int $job_id Job ID
     * @return object|null Job object or null
     */
    public static function get_job( $job_id ) {
        global $wpdb;
        
        self::init();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is a safe class constant.
        $job = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` WHERE id = %d", $job_id )
        );

        if ( $job ) {
            $job->post_types = maybe_unserialize( $job->post_types );
            $job->settings   = maybe_unserialize( $job->settings );
        }

        return $job;
    }

    /**
     * Get all jobs with pagination
     *
     * @param array $args Query arguments
     * @return array Jobs array with pagination info
     */
    public static function get_jobs( $args = array() ) {
        global $wpdb;
        
        self::init();

        $defaults = array(
            'per_page' => 20,
            'page'     => 1,
            'status'   => '',
            'orderby'  => 'created_at',
            'order'    => 'DESC',
        );

        $args = wp_parse_args( $args, $defaults );

        $per_page = max( 1, absint( $args['per_page'] ) );
        $page     = max( 1, absint( $args['page'] ) );
        $offset   = ( $page - 1 ) * $per_page;

        $allowed_statuses = array( 'pending', 'processing', 'paused', 'completed', 'failed', 'cancelled' );
        $status           = '';
        if ( ! empty( $args['status'] ) ) {
            $maybe_status = sanitize_key( (string) $args['status'] );
            if ( in_array( $maybe_status, $allowed_statuses, true ) ) {
                $status = $maybe_status;
            }
        }

        $allowed_orderby = array( 'id', 'job_name', 'job_status', 'created_at', 'updated_at' );
        $orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
        $order           = strtoupper( (string) $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

        $jobs  = array();
        $total = 0;

        if ( $status === '' ) {
            if ( $orderby === 'id' ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $jobs = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` ORDER BY id ASC LIMIT %d OFFSET %d", $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset ) );
            } elseif ( $orderby === 'job_name' ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $jobs = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` ORDER BY job_name ASC LIMIT %d OFFSET %d", $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` ORDER BY job_name DESC LIMIT %d OFFSET %d", $per_page, $offset ) );
            } elseif ( $orderby === 'job_status' ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $jobs = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` ORDER BY job_status ASC LIMIT %d OFFSET %d", $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` ORDER BY job_status DESC LIMIT %d OFFSET %d", $per_page, $offset ) );
            } elseif ( $orderby === 'updated_at' ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $jobs = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` ORDER BY updated_at ASC LIMIT %d OFFSET %d", $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` ORDER BY updated_at DESC LIMIT %d OFFSET %d", $per_page, $offset ) );
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $jobs = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` ORDER BY created_at ASC LIMIT %d OFFSET %d", $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset ) );
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned.
            $total = $wpdb->get_var( "SELECT COUNT(*) FROM `" . esc_sql( self::$table_jobs ) . "`" );
        } else {
            if ( $orderby === 'id' ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $jobs = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` WHERE job_status = %s ORDER BY id ASC LIMIT %d OFFSET %d", $status, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` WHERE job_status = %s ORDER BY id DESC LIMIT %d OFFSET %d", $status, $per_page, $offset ) );
            } elseif ( $orderby === 'job_name' ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $jobs = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` WHERE job_status = %s ORDER BY job_name ASC LIMIT %d OFFSET %d", $status, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` WHERE job_status = %s ORDER BY job_name DESC LIMIT %d OFFSET %d", $status, $per_page, $offset ) );
            } elseif ( $orderby === 'job_status' ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $jobs = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` WHERE job_status = %s ORDER BY job_status ASC LIMIT %d OFFSET %d", $status, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` WHERE job_status = %s ORDER BY job_status DESC LIMIT %d OFFSET %d", $status, $per_page, $offset ) );
            } elseif ( $orderby === 'updated_at' ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $jobs = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` WHERE job_status = %s ORDER BY updated_at ASC LIMIT %d OFFSET %d", $status, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` WHERE job_status = %s ORDER BY updated_at DESC LIMIT %d OFFSET %d", $status, $per_page, $offset ) );
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $jobs = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` WHERE job_status = %s ORDER BY created_at ASC LIMIT %d OFFSET %d", $status, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` WHERE job_status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d", $status, $per_page, $offset ) );
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned.
            $total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `" . esc_sql( self::$table_jobs ) . "` WHERE job_status = %s", $status ) );
        }

        foreach ( $jobs as &$job ) {
            $job->post_types = maybe_unserialize( $job->post_types );
            $job->settings   = maybe_unserialize( $job->settings );
        }

        return array(
            'jobs'       => $jobs,
            'total'      => (int) $total,
            'pages'      => ceil( $total / $per_page ),
            'page'       => $page,
            'per_page'   => $per_page,
        );
    }

    /**
     * Update a job
     *
     * @param int   $job_id Job ID
     * @param array $data   Data to update
     * @return bool Success
     */
    public static function update_job( $job_id, $data ) {
        global $wpdb;
        
        self::init();

        // Serialize arrays
        if ( isset( $data['post_types'] ) && is_array( $data['post_types'] ) ) {
            $data['post_types'] = maybe_serialize( $data['post_types'] );
        }
        if ( isset( $data['settings'] ) && is_array( $data['settings'] ) ) {
            $data['settings'] = maybe_serialize( $data['settings'] );
        }

        $result = $wpdb->update(
            self::$table_jobs,
            $data,
            array( 'id' => $job_id ),
            null,
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Delete a job and all its posts
     *
     * @param int $job_id Job ID
     * @return bool Success
     */
    public static function delete_job( $job_id ) {
        global $wpdb;
        
        self::init();

        // Posts are deleted via CASCADE
        $result = $wpdb->delete(
            self::$table_jobs,
            array( 'id' => $job_id ),
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Update job status
     *
     * @param int    $job_id Job ID
     * @param string $status New status
     * @return bool Success
     */
    public static function update_job_status( $job_id, $status ) {
        $data = array( 'job_status' => $status );
        
        if ( $status === 'processing' ) {
            $data['started_at'] = current_time( 'mysql' );
        } elseif ( in_array( $status, array( 'completed', 'failed', 'cancelled' ) ) ) {
            $data['completed_at'] = current_time( 'mysql' );
        }

        return self::update_job( $job_id, $data );
    }

    /**
     * Increment job counters
     *
     * @param int    $job_id  Job ID
     * @param string $counter Counter name (processed_posts, successful_posts, failed_posts)
     * @param int    $amount  Amount to increment
     * @return bool Success
     */
    public static function increment_job_counter( $job_id, $counter, $amount = 1 ) {
        global $wpdb;
        
        self::init();

        $job_id = absint( $job_id );
        $amount = absint( $amount );

        if ( $counter === 'processed_posts' ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned.
            $result = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE `" . esc_sql( self::$table_jobs ) . "` SET processed_posts = processed_posts + %d WHERE id = %d",
                    $amount,
                    $job_id
                )
            );
        } elseif ( $counter === 'successful_posts' ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned.
            $result = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE `" . esc_sql( self::$table_jobs ) . "` SET successful_posts = successful_posts + %d WHERE id = %d",
                    $amount,
                    $job_id
                )
            );
        } elseif ( $counter === 'failed_posts' ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned.
            $result = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE `" . esc_sql( self::$table_jobs ) . "` SET failed_posts = failed_posts + %d WHERE id = %d",
                    $amount,
                    $job_id
                )
            );
        } else {
            return false;
        }

        return $result !== false;
    }

    // =====================
    // POSTS CRUD Operations
    // =====================

    /**
     * Add posts to a job
     *
     * @param int   $job_id   Job ID
     * @param array $post_ids Array of post IDs
     * @return int Number of posts added
     */
    public static function add_posts_to_job( $job_id, $post_ids ) {
        global $wpdb;
        
        self::init();

        $added = 0;

        foreach ( $post_ids as $post_id ) {
            $post = get_post( $post_id );
            if ( ! $post ) {
                continue;
            }

            $result = $wpdb->insert(
                self::$table_posts,
                array(
                    'job_id'     => absint( $job_id ),
                    'post_id'    => absint( $post_id ),
                    'post_type'  => $post->post_type,
                    'post_title' => $post->post_title,
                    'status'     => 'pending',
                    'featured_image_status' => 'pending',
                ),
                array( '%d', '%d', '%s', '%s', '%s', '%s' )
            );

            if ( $result ) {
                $added++;
            }
        }

        // Update job total
        self::update_job( $job_id, array( 'total_posts' => $added ) );

        return $added;
    }

    /**
     * Get posts for a job
     *
     * @param int   $job_id Job ID
     * @param array $args   Query arguments
     * @return array Posts array
     */
    public static function get_job_posts( $job_id, $args = array() ) {
        global $wpdb;
        
        self::init();

        $defaults = array(
            'per_page' => 50,
            'page'     => 1,
            'status'   => '',
            'orderby'  => 'id',
            'order'    => 'ASC',
        );

        $args = wp_parse_args( $args, $defaults );

        $job_id   = absint( $job_id );
        $per_page = max( 1, absint( $args['per_page'] ) );
        $page     = max( 1, absint( $args['page'] ) );
        $offset   = ( $page - 1 ) * $per_page;

        $allowed_statuses = array( 'pending', 'processing', 'completed', 'failed', 'skipped' );
        $status           = '';
        if ( ! empty( $args['status'] ) ) {
            $maybe_status = sanitize_key( (string) $args['status'] );
            if ( in_array( $maybe_status, $allowed_statuses, true ) ) {
                $status = $maybe_status;
            }
        }

        $allowed_orderby = array( 'id', 'post_id', 'post_title', 'status', 'processed_at' );
        $orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'id';
        $order           = strtoupper( (string) $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

        $posts  = array();
        $total  = 0;
        $has_status_filter = ( $status !== '' );

        if ( $orderby === 'id' ) {
            if ( $has_status_filter ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $posts = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d AND status = %s ORDER BY id ASC LIMIT %d OFFSET %d", $job_id, $status, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d AND status = %s ORDER BY id DESC LIMIT %d OFFSET %d", $job_id, $status, $per_page, $offset ) );
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $posts = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d ORDER BY id ASC LIMIT %d OFFSET %d", $job_id, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d ORDER BY id DESC LIMIT %d OFFSET %d", $job_id, $per_page, $offset ) );
            }
        } elseif ( $orderby === 'post_id' ) {
            if ( $has_status_filter ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $posts = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d AND status = %s ORDER BY post_id ASC LIMIT %d OFFSET %d", $job_id, $status, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d AND status = %s ORDER BY post_id DESC LIMIT %d OFFSET %d", $job_id, $status, $per_page, $offset ) );
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $posts = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d ORDER BY post_id ASC LIMIT %d OFFSET %d", $job_id, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d ORDER BY post_id DESC LIMIT %d OFFSET %d", $job_id, $per_page, $offset ) );
            }
        } elseif ( $orderby === 'post_title' ) {
            if ( $has_status_filter ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $posts = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d AND status = %s ORDER BY post_title ASC LIMIT %d OFFSET %d", $job_id, $status, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d AND status = %s ORDER BY post_title DESC LIMIT %d OFFSET %d", $job_id, $status, $per_page, $offset ) );
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $posts = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d ORDER BY post_title ASC LIMIT %d OFFSET %d", $job_id, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d ORDER BY post_title DESC LIMIT %d OFFSET %d", $job_id, $per_page, $offset ) );
            }
        } elseif ( $orderby === 'status' ) {
            if ( $has_status_filter ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $posts = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d AND status = %s ORDER BY status ASC LIMIT %d OFFSET %d", $job_id, $status, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d AND status = %s ORDER BY status DESC LIMIT %d OFFSET %d", $job_id, $status, $per_page, $offset ) );
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $posts = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d ORDER BY status ASC LIMIT %d OFFSET %d", $job_id, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d ORDER BY status DESC LIMIT %d OFFSET %d", $job_id, $per_page, $offset ) );
            }
        } elseif ( $orderby === 'processed_at' ) {
            if ( $has_status_filter ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $posts = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d AND status = %s ORDER BY processed_at ASC LIMIT %d OFFSET %d", $job_id, $status, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d AND status = %s ORDER BY processed_at DESC LIMIT %d OFFSET %d", $job_id, $status, $per_page, $offset ) );
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $posts = ( $order === 'ASC' )
                    ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d ORDER BY processed_at ASC LIMIT %d OFFSET %d", $job_id, $per_page, $offset ) )
                    : $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d ORDER BY processed_at DESC LIMIT %d OFFSET %d", $job_id, $per_page, $offset ) );
            }
        } else {
            if ( $has_status_filter ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $posts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d AND status = %s ORDER BY id ASC LIMIT %d OFFSET %d", $job_id, $status, $per_page, $offset ) );
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned; results are paged.
                $posts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d ORDER BY id ASC LIMIT %d OFFSET %d", $job_id, $per_page, $offset ) );
            }
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk tables are plugin-owned.
        $total = $has_status_filter
            ? $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d AND status = %s", $job_id, $status ) )
            : $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d", $job_id ) );

        foreach ( $posts as &$post ) {
            $post->additional_images = maybe_unserialize( $post->additional_images );
        }

        return array(
            'posts'    => $posts,
            'total'    => (int) $total,
            'pages'    => ceil( $total / $per_page ),
            'page'     => $page,
            'per_page' => $per_page,
        );
    }

    /**
     * Get next pending post from a job
     *
     * @param int $job_id Job ID
     * @return object|null Post object or null
     */
    public static function get_next_pending_post( $job_id ) {
        global $wpdb;
        
        self::init();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is a safe class constant.
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d AND status = 'pending' ORDER BY id ASC LIMIT 1",
                $job_id
            )
        );
    }

    /**
     * Update a job post
     *
     * @param int   $id   Post row ID
     * @param array $data Data to update
     * @return bool Success
     */
    public static function update_job_post( $id, $data ) {
        global $wpdb;
        
        self::init();

        // Serialize arrays
        if ( isset( $data['additional_images'] ) && is_array( $data['additional_images'] ) ) {
            $data['additional_images'] = maybe_serialize( $data['additional_images'] );
        }

        $result = $wpdb->update(
            self::$table_posts,
            $data,
            array( 'id' => $id ),
            null,
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Mark a post as processed
     *
     * @param int    $id              Post row ID
     * @param string $status          Status (completed, failed, skipped)
     * @param array  $additional_data Additional data to save
     * @return bool Success
     */
    public static function mark_post_processed( $id, $status, $additional_data = array() ) {
        $data = array_merge( $additional_data, array(
            'status'       => $status,
            'processed_at' => current_time( 'mysql' ),
        ) );

        return self::update_job_post( $id, $data );
    }

    /**
     * Get job statistics
     *
     * @param int $job_id Job ID
     * @return array Statistics
     */
    public static function get_job_stats( $job_id ) {
        global $wpdb;
        
        self::init();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is a safe class constant.
        $stats = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                    SUM(CASE WHEN status = 'skipped' THEN 1 ELSE 0 END) as skipped
                FROM `" . esc_sql( self::$table_posts ) . "` WHERE job_id = %d",
                $job_id
            ),
            ARRAY_A
        );

        return $stats ? array_map( 'intval', $stats ) : array(
            'total'      => 0,
            'pending'    => 0,
            'processing' => 0,
            'completed'  => 0,
            'failed'     => 0,
            'skipped'    => 0,
        );
    }

    /**
     * Check if there's an active (processing) job
     *
     * @return object|null Active job or null
     */
    public static function get_active_job() {
        global $wpdb;
        
        self::init();

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` WHERE job_status = %s ORDER BY started_at DESC LIMIT 1",
                'processing'
            )
        );
    }

    /**
     * Get pending jobs
     *
     * @param int $limit Number of jobs to return
     * @return array Jobs array
     */
    public static function get_pending_jobs( $limit = 1 ) {
        global $wpdb;
        
        self::init();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is a safe class constant.
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `" . esc_sql( self::$table_jobs ) . "` WHERE job_status = 'pending' ORDER BY created_at ASC LIMIT %d",
                $limit
            )
        );
    }
}

// Initialize on load
ALLSI_Bulk_Generation_DB::init();

// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
