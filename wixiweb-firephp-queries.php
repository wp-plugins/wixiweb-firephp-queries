<?php

/**
 * Plugin Name: Wixiweb Firephp Queries
 * Plugin URI:  http://wixiweb.fr
 * Description: Use Firebug with FirePHP to analyze the SQL queries made by Wordpress. Ideal for development and avoid performance issues.
 * Author:      Arnaud Lemercier
 * Version:     1.0.1
 * Author URI:  http://arnaud.lemercier.me
 */
include_once 'vendor/firephp/fb.php';

new WixiwebFirephpQueries();

class WixiwebFirephpQueries
{

    public function __construct()
    {
	
		if (version_compare(phpversion(), "5.5.0", "<")) {
			include_once('vendor/ramsey/array_column.php');
		}

        add_action('init', function() {

            $firephp = FirePHP::getInstance(true);

            if (!current_user_can('administrator')) {
                return;
            }

            if(!defined('SAVEQUERIES')){
                define( 'SAVEQUERIES', true );
                $firephp->warn('`Wixiweb FirePHP Queries` enabled SAVEQUERIES. Only SQL Queries made after `Wixiweb FirePHP Queries` is logged.');
                $firephp->info('For best results, activate SAVEQUERIES from your wp-config.php file to see all the SQL queries');
            }
        }, 0);

        add_action('wp', function() {
            
            $firephp = FirePHP::getInstance(true);

            if (!current_user_can('administrator')) {
                $firephp->error('You must be logged in as Administrator to use `Wixiweb FirePHP Queries`');
                return;
            }

            if(!SAVEQUERIES){
                $firephp->error('You must switch `SAVEQUERIES` to `true` from `wp-config.php` to capture SQL queries');
                return;
            }

            global $wpdb;
            $queries = is_array($wpdb->queries) ? $wpdb->queries : array();
            
            $nbQueries = count($queries);
            $time = round(array_sum(array_column($queries, 1)), 4);

            array_unshift($queries, array(
                'Queries ('.$nbQueries.')',
                'Time ('.$time.')',
                'Stack'
            ));

            $firephp->table('SQL Queries ('.$nbQueries.' - '.$time.')', $queries);
        }, 999);

    }
}