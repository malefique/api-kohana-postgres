<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
	'alternate' => array
	(
		'type'       => 'MySQL',
		'connection' => array(
			/**
			 * The following options are available for MySQL:
			 *
			 * string   hostname     server hostname, or socket
			 * string   database     database name
			 * string   username     database username
			 * string   password     database password
			 * boolean  persistent   use persistent connections?
			 * array    variables    system variables as "key => value" pairs
			 *
			 * Ports and sockets may be appended to the hostname.
			 */
			'hostname'   => 'localhost',
			'database'   => 'kohana',
			'username'   => FALSE,
			'password'   => FALSE,
			'persistent' => FALSE,
		),
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => FALSE,
	),
    'default' => array
    (
        'type'       => 'PostgreSQL',
        'connection' => array(
            /**
             * There are two ways to define a connection for PostgreSQL:
             *
             * 1. Full connection string passed directly to pg_connect()
             *
             * string   info
             *
             * 2. Connection parameters:
             *
             * string   hostname    NULL to use default domain socket
             * integer  port        NULL to use the default port
             * string   username
             * string   password
             * string   database
             * boolean  persistent
             * mixed    ssl         TRUE to require, FALSE to disable, or 'prefer' to negotiate
             *
             * @link http://www.postgresql.org/docs/current/static/libpq-connect.html
             */
            'hostname'   => '127.0.0.1',
            'username'   => 'worldintim',
            'password'   => 'oleg333222zk',
            'persistent' => FALSE,
            'database'   => 'api',
        ),
        'primary_key'  => '',   // Column to return from INSERT queries, see #2188 and #2273
        'schema'       => '',
        'table_prefix' => '',
        'charset'      => 'utf8',
        'caching'      => FALSE,
    ),
	'pg' => array(
		'type'       => 'PDO',
		'connection' => array(
			/**
			 * The following options are available for PDO:
			 *
			 * string   dsn         Data Source Name
			 * string   username    database username
			 * string   password    database password
			 * boolean  persistent  use persistent connections?
			 */
			'dsn'        => 'pgsql:host=localhost;dbname=api',
			'username'   => 'worldintim',
			'password'   => 'oleg333222zk',
			'persistent' => FALSE,
		),
		/**
		 * The following extra options are available for PDO:
		 *
		 * string   identifier  set the escaping identifier
		 */
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => FALSE,
		'identifier'   => '"',
	),
);
