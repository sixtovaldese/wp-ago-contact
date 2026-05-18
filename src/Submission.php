<?php

namespace AgoLab\Contact;

defined( 'ABSPATH' ) || exit;

class Submission {

    private static function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'ago_contact_submissions';
    }

    public static function create_table(): void {
        global $wpdb;
        $table   = self::table();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) DEFAULT '',
            email varchar(255) DEFAULT '',
            phone varchar(50) DEFAULT '',
            subject varchar(255) DEFAULT '',
            company varchar(255) DEFAULT '',
            department varchar(255) DEFAULT '',
            message text DEFAULT '',
            gdpr tinyint(1) DEFAULT 0,
            ip_address varchar(45) DEFAULT '',
            status varchar(20) DEFAULT 'new',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public static function insert( array $data, string $ip ): int {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table; cached via object cache layer.
        $wpdb->insert( self::table(), [
            'name'       => $data['name'] ?? '',
            'email'      => $data['email'] ?? '',
            'phone'      => $data['phone'] ?? '',
            'subject'    => $data['subject'] ?? '',
            'company'    => $data['company'] ?? '',
            'department' => $data['department'] ?? '',
            'message'    => $data['message'] ?? '',
            'gdpr'       => ! empty( $data['gdpr'] ) ? 1 : 0,
            'ip_address' => $ip,
            'status'     => 'new',
            'created_at' => current_time( 'mysql' ),
        ] );
        return (int) $wpdb->insert_id;
    }

    public static function get( int $id ): ?object {
        global $wpdb;
        $table = self::table();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom table name interpolated; $id is prepared.
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
        return $row ?: null;
    }

    public static function get_list( string $status = '', int $page = 1, int $per_page = 20 ): array {
        global $wpdb;
        $table  = self::table();
        $offset = ( $page - 1 ) * $per_page;

        $where  = '';
        $params = [];
        if ( $status && in_array( $status, [ 'new', 'read', 'replied', 'spam' ], true ) ) {
            $where    = 'WHERE status = %s';
            $params[] = $status;
        }

        if ( $params ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table interpolated, $status placeholder prepared.
            $total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} {$where}", ...$params ) );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
            $items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d", ...array_merge( $params, [ $per_page, $offset ] ) ) );
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
            $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
            $items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset ) );
        }

        return [
            'items'    => $items,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $per_page,
            'pages'    => (int) ceil( $total / $per_page ),
        ];
    }

    public static function update_status( int $id, string $status ): void {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->update( self::table(), [ 'status' => $status ], [ 'id' => $id ] );
    }

    public static function delete( int $id ): void {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->delete( self::table(), [ 'id' => $id ] );
    }

    public static function count_unread(): int {
        global $wpdb;
        $table = self::table();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'new'" );
    }

    public static function get_all(): array {
        global $wpdb;
        $table = self::table();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC" );
    }

    public static function to_csv( array $items ): string {
        $output = "ID,Name,Email,Phone,Subject,Company,Department,Message,Status,Date\n";
        foreach ( $items as $item ) {
            $output .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $item->id,
                '"' . str_replace( '"', '""', $item->name ) . '"',
                '"' . str_replace( '"', '""', $item->email ) . '"',
                '"' . str_replace( '"', '""', $item->phone ) . '"',
                '"' . str_replace( '"', '""', $item->subject ) . '"',
                '"' . str_replace( '"', '""', $item->company ) . '"',
                '"' . str_replace( '"', '""', $item->department ) . '"',
                '"' . str_replace( '"', '""', $item->message ) . '"',
                $item->status,
                $item->created_at
            );
        }
        return $output;
    }
}
