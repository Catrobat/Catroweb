<?php

// This file is auto-generated and is for apps only. Bundles SHOULD NOT rely on its content.

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * This class provides array-shapes for configuring the services and bundles of an application.
 *
 * Services declared with the config() method below are autowired and autoconfigured by default.
 *
 * This is for apps only. Bundles SHOULD NOT use it.
 *
 * Example:
 *
 *     ```php
 *     // config/services.php
 *     namespace Symfony\Component\DependencyInjection\Loader\Configurator;
 *
 *     return App::config([
 *         'services' => [
 *             'App\\' => [
 *                 'resource' => '../src/',
 *             ],
 *         ],
 *     ]);
 *     ```
 *
 * @psalm-type ImportsConfig = list<string|array{
 *     resource: string,
 *     type?: string|null,
 *     ignore_errors?: bool,
 * }>
 * @psalm-type ParametersConfig = array<string, scalar|\UnitEnum|array<scalar|\UnitEnum|array<mixed>|null>|null>
 * @psalm-type ArgumentsType = list<mixed>|array<string, mixed>
 * @psalm-type CallType = array<string, ArgumentsType>|array{0:string, 1?:ArgumentsType, 2?:bool}|array{method:string, arguments?:ArgumentsType, returns_clone?:bool}
 * @psalm-type TagsType = list<string|array<string, array<string, mixed>>> // arrays inside the list must have only one element, with the tag name as the key
 * @psalm-type CallbackType = string|array{0:string|ReferenceConfigurator,1:string}|\Closure|ReferenceConfigurator|ExpressionConfigurator
 * @psalm-type DeprecationType = array{package: string, version: string, message?: string}
 * @psalm-type DefaultsType = array{
 *     public?: bool,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     autowire?: bool,
 *     autoconfigure?: bool,
 *     bind?: array<string, mixed>,
 * }
 * @psalm-type InstanceofType = array{
 *     shared?: bool,
 *     lazy?: bool|string,
 *     public?: bool,
 *     properties?: array<string, mixed>,
 *     configurator?: CallbackType,
 *     calls?: list<CallType>,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     autowire?: bool,
 *     bind?: array<string, mixed>,
 *     constructor?: string,
 * }
 * @psalm-type DefinitionType = array{
 *     class?: string,
 *     file?: string,
 *     parent?: string,
 *     shared?: bool,
 *     synthetic?: bool,
 *     lazy?: bool|string,
 *     public?: bool,
 *     abstract?: bool,
 *     deprecated?: DeprecationType,
 *     factory?: CallbackType,
 *     configurator?: CallbackType,
 *     arguments?: ArgumentsType,
 *     properties?: array<string, mixed>,
 *     calls?: list<CallType>,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     decorates?: string,
 *     decoration_inner_name?: string,
 *     decoration_priority?: int,
 *     decoration_on_invalid?: 'exception'|'ignore'|null,
 *     autowire?: bool,
 *     autoconfigure?: bool,
 *     bind?: array<string, mixed>,
 *     constructor?: string,
 *     from_callable?: CallbackType,
 * }
 * @psalm-type AliasType = string|array{
 *     alias: string,
 *     public?: bool,
 *     deprecated?: DeprecationType,
 * }
 * @psalm-type PrototypeType = array{
 *     resource: string,
 *     namespace?: string,
 *     exclude?: string|list<string>,
 *     parent?: string,
 *     shared?: bool,
 *     lazy?: bool|string,
 *     public?: bool,
 *     abstract?: bool,
 *     deprecated?: DeprecationType,
 *     factory?: CallbackType,
 *     arguments?: ArgumentsType,
 *     properties?: array<string, mixed>,
 *     configurator?: CallbackType,
 *     calls?: list<CallType>,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     autowire?: bool,
 *     autoconfigure?: bool,
 *     bind?: array<string, mixed>,
 *     constructor?: string,
 * }
 * @psalm-type StackType = array{
 *     stack: list<DefinitionType|AliasType|PrototypeType|array<class-string, ArgumentsType|null>>,
 *     public?: bool,
 *     deprecated?: DeprecationType,
 * }
 * @psalm-type ServicesConfig = array{
 *     _defaults?: DefaultsType,
 *     _instanceof?: InstanceofType,
 *     ...<string, DefinitionType|AliasType|PrototypeType|StackType|ArgumentsType|null>
 * }
 * @psalm-type ExtensionType = array<string, mixed>
 * @psalm-type FrameworkConfig = array{
 *     secret?: scalar|null,
 *     http_method_override?: bool, // Set true to enable support for the '_method' request parameter to determine the intended HTTP method on POST requests. // Default: false
 *     allowed_http_method_override?: list<string>|null,
 *     trust_x_sendfile_type_header?: scalar|null, // Set true to enable support for xsendfile in binary file responses. // Default: "%env(bool:default::SYMFONY_TRUST_X_SENDFILE_TYPE_HEADER)%"
 *     ide?: scalar|null, // Default: "%env(default::SYMFONY_IDE)%"
 *     test?: bool,
 *     default_locale?: scalar|null, // Default: "en"
 *     set_locale_from_accept_language?: bool, // Whether to use the Accept-Language HTTP header to set the Request locale (only when the "_locale" request attribute is not passed). // Default: false
 *     set_content_language_from_locale?: bool, // Whether to set the Content-Language HTTP header on the Response using the Request locale. // Default: false
 *     enabled_locales?: list<scalar|null>,
 *     trusted_hosts?: list<scalar|null>,
 *     trusted_proxies?: mixed, // Default: ["%env(default::SYMFONY_TRUSTED_PROXIES)%"]
 *     trusted_headers?: list<scalar|null>,
 *     error_controller?: scalar|null, // Default: "error_controller"
 *     handle_all_throwables?: bool, // HttpKernel will handle all kinds of \Throwable. // Default: true
 *     csrf_protection?: bool|array{
 *         enabled?: scalar|null, // Default: null
 *         stateless_token_ids?: list<scalar|null>,
 *         check_header?: scalar|null, // Whether to check the CSRF token in a header in addition to a cookie when using stateless protection. // Default: false
 *         cookie_name?: scalar|null, // The name of the cookie to use when using stateless protection. // Default: "csrf-token"
 *     },
 *     form?: bool|array{ // Form configuration
 *         enabled?: bool, // Default: true
 *         csrf_protection?: array{
 *             enabled?: scalar|null, // Default: null
 *             token_id?: scalar|null, // Default: null
 *             field_name?: scalar|null, // Default: "_token"
 *             field_attr?: array<string, scalar|null>,
 *         },
 *     },
 *     http_cache?: bool|array{ // HTTP cache configuration
 *         enabled?: bool, // Default: false
 *         debug?: bool, // Default: "%kernel.debug%"
 *         trace_level?: "none"|"short"|"full",
 *         trace_header?: scalar|null,
 *         default_ttl?: int,
 *         private_headers?: list<scalar|null>,
 *         skip_response_headers?: list<scalar|null>,
 *         allow_reload?: bool,
 *         allow_revalidate?: bool,
 *         stale_while_revalidate?: int,
 *         stale_if_error?: int,
 *         terminate_on_cache_hit?: bool,
 *     },
 *     esi?: bool|array{ // ESI configuration
 *         enabled?: bool, // Default: false
 *     },
 *     ssi?: bool|array{ // SSI configuration
 *         enabled?: bool, // Default: false
 *     },
 *     fragments?: bool|array{ // Fragments configuration
 *         enabled?: bool, // Default: false
 *         hinclude_default_template?: scalar|null, // Default: null
 *         path?: scalar|null, // Default: "/_fragment"
 *     },
 *     profiler?: bool|array{ // Profiler configuration
 *         enabled?: bool, // Default: false
 *         collect?: bool, // Default: true
 *         collect_parameter?: scalar|null, // The name of the parameter to use to enable or disable collection on a per request basis. // Default: null
 *         only_exceptions?: bool, // Default: false
 *         only_main_requests?: bool, // Default: false
 *         dsn?: scalar|null, // Default: "file:%kernel.cache_dir%/profiler"
 *         collect_serializer_data?: bool, // Enables the serializer data collector and profiler panel. // Default: false
 *     },
 *     workflows?: bool|array{
 *         enabled?: bool, // Default: false
 *         workflows?: array<string, array{ // Default: []
 *             audit_trail?: bool|array{
 *                 enabled?: bool, // Default: false
 *             },
 *             type?: "workflow"|"state_machine", // Default: "state_machine"
 *             marking_store?: array{
 *                 type?: "method",
 *                 property?: scalar|null,
 *                 service?: scalar|null,
 *             },
 *             supports?: list<scalar|null>,
 *             definition_validators?: list<scalar|null>,
 *             support_strategy?: scalar|null,
 *             initial_marking?: list<scalar|null>,
 *             events_to_dispatch?: list<string>|null,
 *             places?: list<array{ // Default: []
 *                 name: scalar|null,
 *                 metadata?: list<mixed>,
 *             }>,
 *             transitions: list<array{ // Default: []
 *                 name: string,
 *                 guard?: string, // An expression to block the transition.
 *                 from?: list<array{ // Default: []
 *                     place: string,
 *                     weight?: int, // Default: 1
 *                 }>,
 *                 to?: list<array{ // Default: []
 *                     place: string,
 *                     weight?: int, // Default: 1
 *                 }>,
 *                 weight?: int, // Default: 1
 *                 metadata?: list<mixed>,
 *             }>,
 *             metadata?: list<mixed>,
 *         }>,
 *     },
 *     router?: bool|array{ // Router configuration
 *         enabled?: bool, // Default: false
 *         resource: scalar|null,
 *         type?: scalar|null,
 *         cache_dir?: scalar|null, // Deprecated: Setting the "framework.router.cache_dir.cache_dir" configuration option is deprecated. It will be removed in version 8.0. // Default: "%kernel.build_dir%"
 *         default_uri?: scalar|null, // The default URI used to generate URLs in a non-HTTP context. // Default: null
 *         http_port?: scalar|null, // Default: 80
 *         https_port?: scalar|null, // Default: 443
 *         strict_requirements?: scalar|null, // set to true to throw an exception when a parameter does not match the requirements set to false to disable exceptions when a parameter does not match the requirements (and return null instead) set to null to disable parameter checks against requirements 'true' is the preferred configuration in development mode, while 'false' or 'null' might be preferred in production // Default: true
 *         utf8?: bool, // Default: true
 *     },
 *     session?: bool|array{ // Session configuration
 *         enabled?: bool, // Default: false
 *         storage_factory_id?: scalar|null, // Default: "session.storage.factory.native"
 *         handler_id?: scalar|null, // Defaults to using the native session handler, or to the native *file* session handler if "save_path" is not null.
 *         name?: scalar|null,
 *         cookie_lifetime?: scalar|null,
 *         cookie_path?: scalar|null,
 *         cookie_domain?: scalar|null,
 *         cookie_secure?: true|false|"auto", // Default: "auto"
 *         cookie_httponly?: bool, // Default: true
 *         cookie_samesite?: null|"lax"|"strict"|"none", // Default: "lax"
 *         use_cookies?: bool,
 *         gc_divisor?: scalar|null,
 *         gc_probability?: scalar|null,
 *         gc_maxlifetime?: scalar|null,
 *         save_path?: scalar|null, // Defaults to "%kernel.cache_dir%/sessions" if the "handler_id" option is not null.
 *         metadata_update_threshold?: int, // Seconds to wait between 2 session metadata updates. // Default: 0
 *         sid_length?: int, // Deprecated: Setting the "framework.session.sid_length.sid_length" configuration option is deprecated. It will be removed in version 8.0. No alternative is provided as PHP 8.4 has deprecated the related option.
 *         sid_bits_per_character?: int, // Deprecated: Setting the "framework.session.sid_bits_per_character.sid_bits_per_character" configuration option is deprecated. It will be removed in version 8.0. No alternative is provided as PHP 8.4 has deprecated the related option.
 *     },
 *     request?: bool|array{ // Request configuration
 *         enabled?: bool, // Default: false
 *         formats?: array<string, string|list<scalar|null>>,
 *     },
 *     assets?: bool|array{ // Assets configuration
 *         enabled?: bool, // Default: true
 *         strict_mode?: bool, // Throw an exception if an entry is missing from the manifest.json. // Default: false
 *         version_strategy?: scalar|null, // Default: null
 *         version?: scalar|null, // Default: null
 *         version_format?: scalar|null, // Default: "%%s?%%s"
 *         json_manifest_path?: scalar|null, // Default: null
 *         base_path?: scalar|null, // Default: ""
 *         base_urls?: list<scalar|null>,
 *         packages?: array<string, array{ // Default: []
 *             strict_mode?: bool, // Throw an exception if an entry is missing from the manifest.json. // Default: false
 *             version_strategy?: scalar|null, // Default: null
 *             version?: scalar|null,
 *             version_format?: scalar|null, // Default: null
 *             json_manifest_path?: scalar|null, // Default: null
 *             base_path?: scalar|null, // Default: ""
 *             base_urls?: list<scalar|null>,
 *         }>,
 *     },
 *     asset_mapper?: bool|array{ // Asset Mapper configuration
 *         enabled?: bool, // Default: false
 *         paths?: array<string, scalar|null>,
 *         excluded_patterns?: list<scalar|null>,
 *         exclude_dotfiles?: bool, // If true, any files starting with "." will be excluded from the asset mapper. // Default: true
 *         server?: bool, // If true, a "dev server" will return the assets from the public directory (true in "debug" mode only by default). // Default: true
 *         public_prefix?: scalar|null, // The public path where the assets will be written to (and served from when "server" is true). // Default: "/assets/"
 *         missing_import_mode?: "strict"|"warn"|"ignore", // Behavior if an asset cannot be found when imported from JavaScript or CSS files - e.g. "import './non-existent.js'". "strict" means an exception is thrown, "warn" means a warning is logged, "ignore" means the import is left as-is. // Default: "warn"
 *         extensions?: array<string, scalar|null>,
 *         importmap_path?: scalar|null, // The path of the importmap.php file. // Default: "%kernel.project_dir%/importmap.php"
 *         importmap_polyfill?: scalar|null, // The importmap name that will be used to load the polyfill. Set to false to disable. // Default: "es-module-shims"
 *         importmap_script_attributes?: array<string, scalar|null>,
 *         vendor_dir?: scalar|null, // The directory to store JavaScript vendors. // Default: "%kernel.project_dir%/assets/vendor"
 *         precompress?: bool|array{ // Precompress assets with Brotli, Zstandard and gzip.
 *             enabled?: bool, // Default: false
 *             formats?: list<scalar|null>,
 *             extensions?: list<scalar|null>,
 *         },
 *     },
 *     translator?: bool|array{ // Translator configuration
 *         enabled?: bool, // Default: true
 *         fallbacks?: list<scalar|null>,
 *         logging?: bool, // Default: false
 *         formatter?: scalar|null, // Default: "translator.formatter.default"
 *         cache_dir?: scalar|null, // Default: "%kernel.cache_dir%/translations"
 *         default_path?: scalar|null, // The default path used to load translations. // Default: "%kernel.project_dir%/translations"
 *         paths?: list<scalar|null>,
 *         pseudo_localization?: bool|array{
 *             enabled?: bool, // Default: false
 *             accents?: bool, // Default: true
 *             expansion_factor?: float, // Default: 1.0
 *             brackets?: bool, // Default: true
 *             parse_html?: bool, // Default: false
 *             localizable_html_attributes?: list<scalar|null>,
 *         },
 *         providers?: array<string, array{ // Default: []
 *             dsn?: scalar|null,
 *             domains?: list<scalar|null>,
 *             locales?: list<scalar|null>,
 *         }>,
 *         globals?: array<string, string|array{ // Default: []
 *             value?: mixed,
 *             message?: string,
 *             parameters?: array<string, scalar|null>,
 *             domain?: string,
 *         }>,
 *     },
 *     validation?: bool|array{ // Validation configuration
 *         enabled?: bool, // Default: true
 *         cache?: scalar|null, // Deprecated: Setting the "framework.validation.cache.cache" configuration option is deprecated. It will be removed in version 8.0.
 *         enable_attributes?: bool, // Default: true
 *         static_method?: list<scalar|null>,
 *         translation_domain?: scalar|null, // Default: "validators"
 *         email_validation_mode?: "html5"|"html5-allow-no-tld"|"strict"|"loose", // Default: "html5"
 *         mapping?: array{
 *             paths?: list<scalar|null>,
 *         },
 *         not_compromised_password?: bool|array{
 *             enabled?: bool, // When disabled, compromised passwords will be accepted as valid. // Default: true
 *             endpoint?: scalar|null, // API endpoint for the NotCompromisedPassword Validator. // Default: null
 *         },
 *         disable_translation?: bool, // Default: false
 *         auto_mapping?: array<string, array{ // Default: []
 *             services?: list<scalar|null>,
 *         }>,
 *     },
 *     annotations?: bool|array{
 *         enabled?: bool, // Default: false
 *     },
 *     serializer?: bool|array{ // Serializer configuration
 *         enabled?: bool, // Default: true
 *         enable_attributes?: bool, // Default: true
 *         name_converter?: scalar|null,
 *         circular_reference_handler?: scalar|null,
 *         max_depth_handler?: scalar|null,
 *         mapping?: array{
 *             paths?: list<scalar|null>,
 *         },
 *         default_context?: list<mixed>,
 *         named_serializers?: array<string, array{ // Default: []
 *             name_converter?: scalar|null,
 *             default_context?: list<mixed>,
 *             include_built_in_normalizers?: bool, // Whether to include the built-in normalizers // Default: true
 *             include_built_in_encoders?: bool, // Whether to include the built-in encoders // Default: true
 *         }>,
 *     },
 *     property_access?: bool|array{ // Property access configuration
 *         enabled?: bool, // Default: true
 *         magic_call?: bool, // Default: false
 *         magic_get?: bool, // Default: true
 *         magic_set?: bool, // Default: true
 *         throw_exception_on_invalid_index?: bool, // Default: false
 *         throw_exception_on_invalid_property_path?: bool, // Default: true
 *     },
 *     type_info?: bool|array{ // Type info configuration
 *         enabled?: bool, // Default: true
 *         aliases?: array<string, scalar|null>,
 *     },
 *     property_info?: bool|array{ // Property info configuration
 *         enabled?: bool, // Default: true
 *         with_constructor_extractor?: bool, // Registers the constructor extractor.
 *     },
 *     cache?: array{ // Cache configuration
 *         prefix_seed?: scalar|null, // Used to namespace cache keys when using several apps with the same shared backend. // Default: "_%kernel.project_dir%.%kernel.container_class%"
 *         app?: scalar|null, // App related cache pools configuration. // Default: "cache.adapter.filesystem"
 *         system?: scalar|null, // System related cache pools configuration. // Default: "cache.adapter.system"
 *         directory?: scalar|null, // Default: "%kernel.share_dir%/pools/app"
 *         default_psr6_provider?: scalar|null,
 *         default_redis_provider?: scalar|null, // Default: "redis://localhost"
 *         default_valkey_provider?: scalar|null, // Default: "valkey://localhost"
 *         default_memcached_provider?: scalar|null, // Default: "memcached://localhost"
 *         default_doctrine_dbal_provider?: scalar|null, // Default: "database_connection"
 *         default_pdo_provider?: scalar|null, // Default: null
 *         pools?: array<string, array{ // Default: []
 *             adapters?: list<scalar|null>,
 *             tags?: scalar|null, // Default: null
 *             public?: bool, // Default: false
 *             default_lifetime?: scalar|null, // Default lifetime of the pool.
 *             provider?: scalar|null, // Overwrite the setting from the default provider for this adapter.
 *             early_expiration_message_bus?: scalar|null,
 *             clearer?: scalar|null,
 *         }>,
 *     },
 *     php_errors?: array{ // PHP errors handling configuration
 *         log?: mixed, // Use the application logger instead of the PHP logger for logging PHP errors. // Default: true
 *         throw?: bool, // Throw PHP errors as \ErrorException instances. // Default: true
 *     },
 *     exceptions?: array<string, array{ // Default: []
 *         log_level?: scalar|null, // The level of log message. Null to let Symfony decide. // Default: null
 *         status_code?: scalar|null, // The status code of the response. Null or 0 to let Symfony decide. // Default: null
 *         log_channel?: scalar|null, // The channel of log message. Null to let Symfony decide. // Default: null
 *     }>,
 *     web_link?: bool|array{ // Web links configuration
 *         enabled?: bool, // Default: false
 *     },
 *     lock?: bool|string|array{ // Lock configuration
 *         enabled?: bool, // Default: false
 *         resources?: array<string, string|list<scalar|null>>,
 *     },
 *     semaphore?: bool|string|array{ // Semaphore configuration
 *         enabled?: bool, // Default: false
 *         resources?: array<string, scalar|null>,
 *     },
 *     messenger?: bool|array{ // Messenger configuration
 *         enabled?: bool, // Default: false
 *         routing?: array<string, array{ // Default: []
 *             senders?: list<scalar|null>,
 *         }>,
 *         serializer?: array{
 *             default_serializer?: scalar|null, // Service id to use as the default serializer for the transports. // Default: "messenger.transport.native_php_serializer"
 *             symfony_serializer?: array{
 *                 format?: scalar|null, // Serialization format for the messenger.transport.symfony_serializer service (which is not the serializer used by default). // Default: "json"
 *                 context?: array<string, mixed>,
 *             },
 *         },
 *         transports?: array<string, string|array{ // Default: []
 *             dsn?: scalar|null,
 *             serializer?: scalar|null, // Service id of a custom serializer to use. // Default: null
 *             options?: list<mixed>,
 *             failure_transport?: scalar|null, // Transport name to send failed messages to (after all retries have failed). // Default: null
 *             retry_strategy?: string|array{
 *                 service?: scalar|null, // Service id to override the retry strategy entirely. // Default: null
 *                 max_retries?: int, // Default: 3
 *                 delay?: int, // Time in ms to delay (or the initial value when multiplier is used). // Default: 1000
 *                 multiplier?: float, // If greater than 1, delay will grow exponentially for each retry: this delay = (delay * (multiple ^ retries)). // Default: 2
 *                 max_delay?: int, // Max time in ms that a retry should ever be delayed (0 = infinite). // Default: 0
 *                 jitter?: float, // Randomness to apply to the delay (between 0 and 1). // Default: 0.1
 *             },
 *             rate_limiter?: scalar|null, // Rate limiter name to use when processing messages. // Default: null
 *         }>,
 *         failure_transport?: scalar|null, // Transport name to send failed messages to (after all retries have failed). // Default: null
 *         stop_worker_on_signals?: list<scalar|null>,
 *         default_bus?: scalar|null, // Default: null
 *         buses?: array<string, array{ // Default: {"messenger.bus.default":{"default_middleware":{"enabled":true,"allow_no_handlers":false,"allow_no_senders":true},"middleware":[]}}
 *             default_middleware?: bool|string|array{
 *                 enabled?: bool, // Default: true
 *                 allow_no_handlers?: bool, // Default: false
 *                 allow_no_senders?: bool, // Default: true
 *             },
 *             middleware?: list<string|array{ // Default: []
 *                 id: scalar|null,
 *                 arguments?: list<mixed>,
 *             }>,
 *         }>,
 *     },
 *     scheduler?: bool|array{ // Scheduler configuration
 *         enabled?: bool, // Default: false
 *     },
 *     disallow_search_engine_index?: bool, // Enabled by default when debug is enabled. // Default: true
 *     http_client?: bool|array{ // HTTP Client configuration
 *         enabled?: bool, // Default: true
 *         max_host_connections?: int, // The maximum number of connections to a single host.
 *         default_options?: array{
 *             headers?: array<string, mixed>,
 *             vars?: array<string, mixed>,
 *             max_redirects?: int, // The maximum number of redirects to follow.
 *             http_version?: scalar|null, // The default HTTP version, typically 1.1 or 2.0, leave to null for the best version.
 *             resolve?: array<string, scalar|null>,
 *             proxy?: scalar|null, // The URL of the proxy to pass requests through or null for automatic detection.
 *             no_proxy?: scalar|null, // A comma separated list of hosts that do not require a proxy to be reached.
 *             timeout?: float, // The idle timeout, defaults to the "default_socket_timeout" ini parameter.
 *             max_duration?: float, // The maximum execution time for the request+response as a whole.
 *             bindto?: scalar|null, // A network interface name, IP address, a host name or a UNIX socket to bind to.
 *             verify_peer?: bool, // Indicates if the peer should be verified in a TLS context.
 *             verify_host?: bool, // Indicates if the host should exist as a certificate common name.
 *             cafile?: scalar|null, // A certificate authority file.
 *             capath?: scalar|null, // A directory that contains multiple certificate authority files.
 *             local_cert?: scalar|null, // A PEM formatted certificate file.
 *             local_pk?: scalar|null, // A private key file.
 *             passphrase?: scalar|null, // The passphrase used to encrypt the "local_pk" file.
 *             ciphers?: scalar|null, // A list of TLS ciphers separated by colons, commas or spaces (e.g. "RC3-SHA:TLS13-AES-128-GCM-SHA256"...)
 *             peer_fingerprint?: array{ // Associative array: hashing algorithm => hash(es).
 *                 sha1?: mixed,
 *                 pin-sha256?: mixed,
 *                 md5?: mixed,
 *             },
 *             crypto_method?: scalar|null, // The minimum version of TLS to accept; must be one of STREAM_CRYPTO_METHOD_TLSv*_CLIENT constants.
 *             extra?: array<string, mixed>,
 *             rate_limiter?: scalar|null, // Rate limiter name to use for throttling requests. // Default: null
 *             caching?: bool|array{ // Caching configuration.
 *                 enabled?: bool, // Default: false
 *                 cache_pool?: string, // The taggable cache pool to use for storing the responses. // Default: "cache.http_client"
 *                 shared?: bool, // Indicates whether the cache is shared (public) or private. // Default: true
 *                 max_ttl?: int, // The maximum TTL (in seconds) allowed for cached responses. Null means no cap. // Default: null
 *             },
 *             retry_failed?: bool|array{
 *                 enabled?: bool, // Default: false
 *                 retry_strategy?: scalar|null, // service id to override the retry strategy. // Default: null
 *                 http_codes?: array<string, array{ // Default: []
 *                     code?: int,
 *                     methods?: list<string>,
 *                 }>,
 *                 max_retries?: int, // Default: 3
 *                 delay?: int, // Time in ms to delay (or the initial value when multiplier is used). // Default: 1000
 *                 multiplier?: float, // If greater than 1, delay will grow exponentially for each retry: delay * (multiple ^ retries). // Default: 2
 *                 max_delay?: int, // Max time in ms that a retry should ever be delayed (0 = infinite). // Default: 0
 *                 jitter?: float, // Randomness in percent (between 0 and 1) to apply to the delay. // Default: 0.1
 *             },
 *         },
 *         mock_response_factory?: scalar|null, // The id of the service that should generate mock responses. It should be either an invokable or an iterable.
 *         scoped_clients?: array<string, string|array{ // Default: []
 *             scope?: scalar|null, // The regular expression that the request URL must match before adding the other options. When none is provided, the base URI is used instead.
 *             base_uri?: scalar|null, // The URI to resolve relative URLs, following rules in RFC 3985, section 2.
 *             auth_basic?: scalar|null, // An HTTP Basic authentication "username:password".
 *             auth_bearer?: scalar|null, // A token enabling HTTP Bearer authorization.
 *             auth_ntlm?: scalar|null, // A "username:password" pair to use Microsoft NTLM authentication (requires the cURL extension).
 *             query?: array<string, scalar|null>,
 *             headers?: array<string, mixed>,
 *             max_redirects?: int, // The maximum number of redirects to follow.
 *             http_version?: scalar|null, // The default HTTP version, typically 1.1 or 2.0, leave to null for the best version.
 *             resolve?: array<string, scalar|null>,
 *             proxy?: scalar|null, // The URL of the proxy to pass requests through or null for automatic detection.
 *             no_proxy?: scalar|null, // A comma separated list of hosts that do not require a proxy to be reached.
 *             timeout?: float, // The idle timeout, defaults to the "default_socket_timeout" ini parameter.
 *             max_duration?: float, // The maximum execution time for the request+response as a whole.
 *             bindto?: scalar|null, // A network interface name, IP address, a host name or a UNIX socket to bind to.
 *             verify_peer?: bool, // Indicates if the peer should be verified in a TLS context.
 *             verify_host?: bool, // Indicates if the host should exist as a certificate common name.
 *             cafile?: scalar|null, // A certificate authority file.
 *             capath?: scalar|null, // A directory that contains multiple certificate authority files.
 *             local_cert?: scalar|null, // A PEM formatted certificate file.
 *             local_pk?: scalar|null, // A private key file.
 *             passphrase?: scalar|null, // The passphrase used to encrypt the "local_pk" file.
 *             ciphers?: scalar|null, // A list of TLS ciphers separated by colons, commas or spaces (e.g. "RC3-SHA:TLS13-AES-128-GCM-SHA256"...).
 *             peer_fingerprint?: array{ // Associative array: hashing algorithm => hash(es).
 *                 sha1?: mixed,
 *                 pin-sha256?: mixed,
 *                 md5?: mixed,
 *             },
 *             crypto_method?: scalar|null, // The minimum version of TLS to accept; must be one of STREAM_CRYPTO_METHOD_TLSv*_CLIENT constants.
 *             extra?: array<string, mixed>,
 *             rate_limiter?: scalar|null, // Rate limiter name to use for throttling requests. // Default: null
 *             caching?: bool|array{ // Caching configuration.
 *                 enabled?: bool, // Default: false
 *                 cache_pool?: string, // The taggable cache pool to use for storing the responses. // Default: "cache.http_client"
 *                 shared?: bool, // Indicates whether the cache is shared (public) or private. // Default: true
 *                 max_ttl?: int, // The maximum TTL (in seconds) allowed for cached responses. Null means no cap. // Default: null
 *             },
 *             retry_failed?: bool|array{
 *                 enabled?: bool, // Default: false
 *                 retry_strategy?: scalar|null, // service id to override the retry strategy. // Default: null
 *                 http_codes?: array<string, array{ // Default: []
 *                     code?: int,
 *                     methods?: list<string>,
 *                 }>,
 *                 max_retries?: int, // Default: 3
 *                 delay?: int, // Time in ms to delay (or the initial value when multiplier is used). // Default: 1000
 *                 multiplier?: float, // If greater than 1, delay will grow exponentially for each retry: delay * (multiple ^ retries). // Default: 2
 *                 max_delay?: int, // Max time in ms that a retry should ever be delayed (0 = infinite). // Default: 0
 *                 jitter?: float, // Randomness in percent (between 0 and 1) to apply to the delay. // Default: 0.1
 *             },
 *         }>,
 *     },
 *     mailer?: bool|array{ // Mailer configuration
 *         enabled?: bool, // Default: true
 *         message_bus?: scalar|null, // The message bus to use. Defaults to the default bus if the Messenger component is installed. // Default: null
 *         dsn?: scalar|null, // Default: null
 *         transports?: array<string, scalar|null>,
 *         envelope?: array{ // Mailer Envelope configuration
 *             sender?: scalar|null,
 *             recipients?: list<scalar|null>,
 *             allowed_recipients?: list<scalar|null>,
 *         },
 *         headers?: array<string, string|array{ // Default: []
 *             value?: mixed,
 *         }>,
 *         dkim_signer?: bool|array{ // DKIM signer configuration
 *             enabled?: bool, // Default: false
 *             key?: scalar|null, // Key content, or path to key (in PEM format with the `file://` prefix) // Default: ""
 *             domain?: scalar|null, // Default: ""
 *             select?: scalar|null, // Default: ""
 *             passphrase?: scalar|null, // The private key passphrase // Default: ""
 *             options?: array<string, mixed>,
 *         },
 *         smime_signer?: bool|array{ // S/MIME signer configuration
 *             enabled?: bool, // Default: false
 *             key?: scalar|null, // Path to key (in PEM format) // Default: ""
 *             certificate?: scalar|null, // Path to certificate (in PEM format without the `file://` prefix) // Default: ""
 *             passphrase?: scalar|null, // The private key passphrase // Default: null
 *             extra_certificates?: scalar|null, // Default: null
 *             sign_options?: int, // Default: null
 *         },
 *         smime_encrypter?: bool|array{ // S/MIME encrypter configuration
 *             enabled?: bool, // Default: false
 *             repository?: scalar|null, // S/MIME certificate repository service. This service shall implement the `Symfony\Component\Mailer\EventListener\SmimeCertificateRepositoryInterface`. // Default: ""
 *             cipher?: int, // A set of algorithms used to encrypt the message // Default: null
 *         },
 *     },
 *     secrets?: bool|array{
 *         enabled?: bool, // Default: true
 *         vault_directory?: scalar|null, // Default: "%kernel.project_dir%/config/secrets/%kernel.runtime_environment%"
 *         local_dotenv_file?: scalar|null, // Default: "%kernel.project_dir%/.env.%kernel.runtime_environment%.local"
 *         decryption_env_var?: scalar|null, // Default: "base64:default::SYMFONY_DECRYPTION_SECRET"
 *     },
 *     notifier?: bool|array{ // Notifier configuration
 *         enabled?: bool, // Default: false
 *         message_bus?: scalar|null, // The message bus to use. Defaults to the default bus if the Messenger component is installed. // Default: null
 *         chatter_transports?: array<string, scalar|null>,
 *         texter_transports?: array<string, scalar|null>,
 *         notification_on_failed_messages?: bool, // Default: false
 *         channel_policy?: array<string, string|list<scalar|null>>,
 *         admin_recipients?: list<array{ // Default: []
 *             email?: scalar|null,
 *             phone?: scalar|null, // Default: ""
 *         }>,
 *     },
 *     rate_limiter?: bool|array{ // Rate limiter configuration
 *         enabled?: bool, // Default: false
 *         limiters?: array<string, array{ // Default: []
 *             lock_factory?: scalar|null, // The service ID of the lock factory used by this limiter (or null to disable locking). // Default: "auto"
 *             cache_pool?: scalar|null, // The cache pool to use for storing the current limiter state. // Default: "cache.rate_limiter"
 *             storage_service?: scalar|null, // The service ID of a custom storage implementation, this precedes any configured "cache_pool". // Default: null
 *             policy: "fixed_window"|"token_bucket"|"sliding_window"|"compound"|"no_limit", // The algorithm to be used by this limiter.
 *             limiters?: list<scalar|null>,
 *             limit?: int, // The maximum allowed hits in a fixed interval or burst.
 *             interval?: scalar|null, // Configures the fixed interval if "policy" is set to "fixed_window" or "sliding_window". The value must be a number followed by "second", "minute", "hour", "day", "week" or "month" (or their plural equivalent).
 *             rate?: array{ // Configures the fill rate if "policy" is set to "token_bucket".
 *                 interval?: scalar|null, // Configures the rate interval. The value must be a number followed by "second", "minute", "hour", "day", "week" or "month" (or their plural equivalent).
 *                 amount?: int, // Amount of tokens to add each interval. // Default: 1
 *             },
 *         }>,
 *     },
 *     uid?: bool|array{ // Uid configuration
 *         enabled?: bool, // Default: false
 *         default_uuid_version?: 7|6|4|1, // Default: 7
 *         name_based_uuid_version?: 5|3, // Default: 5
 *         name_based_uuid_namespace?: scalar|null,
 *         time_based_uuid_version?: 7|6|1, // Default: 7
 *         time_based_uuid_node?: scalar|null,
 *     },
 *     html_sanitizer?: bool|array{ // HtmlSanitizer configuration
 *         enabled?: bool, // Default: false
 *         sanitizers?: array<string, array{ // Default: []
 *             allow_safe_elements?: bool, // Allows "safe" elements and attributes. // Default: false
 *             allow_static_elements?: bool, // Allows all static elements and attributes from the W3C Sanitizer API standard. // Default: false
 *             allow_elements?: array<string, mixed>,
 *             block_elements?: list<string>,
 *             drop_elements?: list<string>,
 *             allow_attributes?: array<string, mixed>,
 *             drop_attributes?: array<string, mixed>,
 *             force_attributes?: array<string, array<string, string>>,
 *             force_https_urls?: bool, // Transforms URLs using the HTTP scheme to use the HTTPS scheme instead. // Default: false
 *             allowed_link_schemes?: list<string>,
 *             allowed_link_hosts?: list<string>|null,
 *             allow_relative_links?: bool, // Allows relative URLs to be used in links href attributes. // Default: false
 *             allowed_media_schemes?: list<string>,
 *             allowed_media_hosts?: list<string>|null,
 *             allow_relative_medias?: bool, // Allows relative URLs to be used in media source attributes (img, audio, video, ...). // Default: false
 *             with_attribute_sanitizers?: list<string>,
 *             without_attribute_sanitizers?: list<string>,
 *             max_input_length?: int, // The maximum length allowed for the sanitized input. // Default: 0
 *         }>,
 *     },
 *     webhook?: bool|array{ // Webhook configuration
 *         enabled?: bool, // Default: false
 *         message_bus?: scalar|null, // The message bus to use. // Default: "messenger.default_bus"
 *         routing?: array<string, array{ // Default: []
 *             service: scalar|null,
 *             secret?: scalar|null, // Default: ""
 *         }>,
 *     },
 *     remote-event?: bool|array{ // RemoteEvent configuration
 *         enabled?: bool, // Default: false
 *     },
 *     json_streamer?: bool|array{ // JSON streamer configuration
 *         enabled?: bool, // Default: false
 *     },
 * }
 * @psalm-type DoctrineConfig = array{
 *     dbal?: array{
 *         default_connection?: scalar|null,
 *         types?: array<string, string|array{ // Default: []
 *             class: scalar|null,
 *             commented?: bool, // Deprecated: The doctrine-bundle type commenting features were removed; the corresponding config parameter was deprecated in 2.0 and will be dropped in 3.0.
 *         }>,
 *         driver_schemes?: array<string, scalar|null>,
 *         connections?: array<string, array{ // Default: []
 *             url?: scalar|null, // A URL with connection information; any parameter value parsed from this string will override explicitly set parameters
 *             dbname?: scalar|null,
 *             host?: scalar|null, // Defaults to "localhost" at runtime.
 *             port?: scalar|null, // Defaults to null at runtime.
 *             user?: scalar|null, // Defaults to "root" at runtime.
 *             password?: scalar|null, // Defaults to null at runtime.
 *             override_url?: bool, // Deprecated: The "doctrine.dbal.override_url" configuration key is deprecated.
 *             dbname_suffix?: scalar|null, // Adds the given suffix to the configured database name, this option has no effects for the SQLite platform
 *             application_name?: scalar|null,
 *             charset?: scalar|null,
 *             path?: scalar|null,
 *             memory?: bool,
 *             unix_socket?: scalar|null, // The unix socket to use for MySQL
 *             persistent?: bool, // True to use as persistent connection for the ibm_db2 driver
 *             protocol?: scalar|null, // The protocol to use for the ibm_db2 driver (default to TCPIP if omitted)
 *             service?: bool, // True to use SERVICE_NAME as connection parameter instead of SID for Oracle
 *             servicename?: scalar|null, // Overrules dbname parameter if given and used as SERVICE_NAME or SID connection parameter for Oracle depending on the service parameter.
 *             sessionMode?: scalar|null, // The session mode to use for the oci8 driver
 *             server?: scalar|null, // The name of a running database server to connect to for SQL Anywhere.
 *             default_dbname?: scalar|null, // Override the default database (postgres) to connect to for PostgreSQL connexion.
 *             sslmode?: scalar|null, // Determines whether or with what priority a SSL TCP/IP connection will be negotiated with the server for PostgreSQL.
 *             sslrootcert?: scalar|null, // The name of a file containing SSL certificate authority (CA) certificate(s). If the file exists, the server's certificate will be verified to be signed by one of these authorities.
 *             sslcert?: scalar|null, // The path to the SSL client certificate file for PostgreSQL.
 *             sslkey?: scalar|null, // The path to the SSL client key file for PostgreSQL.
 *             sslcrl?: scalar|null, // The file name of the SSL certificate revocation list for PostgreSQL.
 *             pooled?: bool, // True to use a pooled server with the oci8/pdo_oracle driver
 *             MultipleActiveResultSets?: bool, // Configuring MultipleActiveResultSets for the pdo_sqlsrv driver
 *             use_savepoints?: bool, // Use savepoints for nested transactions
 *             instancename?: scalar|null, // Optional parameter, complete whether to add the INSTANCE_NAME parameter in the connection. It is generally used to connect to an Oracle RAC server to select the name of a particular instance.
 *             connectstring?: scalar|null, // Complete Easy Connect connection descriptor, see https://docs.oracle.com/database/121/NETAG/naming.htm.When using this option, you will still need to provide the user and password parameters, but the other parameters will no longer be used. Note that when using this parameter, the getHost and getPort methods from Doctrine\DBAL\Connection will no longer function as expected.
 *             driver?: scalar|null, // Default: "pdo_mysql"
 *             platform_service?: scalar|null, // Deprecated: The "platform_service" configuration key is deprecated since doctrine-bundle 2.9. DBAL 4 will not support setting a custom platform via connection params anymore.
 *             auto_commit?: bool,
 *             schema_filter?: scalar|null,
 *             logging?: bool, // Default: true
 *             profiling?: bool, // Default: true
 *             profiling_collect_backtrace?: bool, // Enables collecting backtraces when profiling is enabled // Default: false
 *             profiling_collect_schema_errors?: bool, // Enables collecting schema errors when profiling is enabled // Default: true
 *             disable_type_comments?: bool,
 *             server_version?: scalar|null,
 *             idle_connection_ttl?: int, // Default: 600
 *             driver_class?: scalar|null,
 *             wrapper_class?: scalar|null,
 *             keep_slave?: bool, // Deprecated: The "keep_slave" configuration key is deprecated since doctrine-bundle 2.2. Use the "keep_replica" configuration key instead.
 *             keep_replica?: bool,
 *             options?: array<string, mixed>,
 *             mapping_types?: array<string, scalar|null>,
 *             default_table_options?: array<string, scalar|null>,
 *             schema_manager_factory?: scalar|null, // Default: "doctrine.dbal.default_schema_manager_factory"
 *             result_cache?: scalar|null,
 *             slaves?: array<string, array{ // Default: []
 *                 url?: scalar|null, // A URL with connection information; any parameter value parsed from this string will override explicitly set parameters
 *                 dbname?: scalar|null,
 *                 host?: scalar|null, // Defaults to "localhost" at runtime.
 *                 port?: scalar|null, // Defaults to null at runtime.
 *                 user?: scalar|null, // Defaults to "root" at runtime.
 *                 password?: scalar|null, // Defaults to null at runtime.
 *                 override_url?: bool, // Deprecated: The "doctrine.dbal.override_url" configuration key is deprecated.
 *                 dbname_suffix?: scalar|null, // Adds the given suffix to the configured database name, this option has no effects for the SQLite platform
 *                 application_name?: scalar|null,
 *                 charset?: scalar|null,
 *                 path?: scalar|null,
 *                 memory?: bool,
 *                 unix_socket?: scalar|null, // The unix socket to use for MySQL
 *                 persistent?: bool, // True to use as persistent connection for the ibm_db2 driver
 *                 protocol?: scalar|null, // The protocol to use for the ibm_db2 driver (default to TCPIP if omitted)
 *                 service?: bool, // True to use SERVICE_NAME as connection parameter instead of SID for Oracle
 *                 servicename?: scalar|null, // Overrules dbname parameter if given and used as SERVICE_NAME or SID connection parameter for Oracle depending on the service parameter.
 *                 sessionMode?: scalar|null, // The session mode to use for the oci8 driver
 *                 server?: scalar|null, // The name of a running database server to connect to for SQL Anywhere.
 *                 default_dbname?: scalar|null, // Override the default database (postgres) to connect to for PostgreSQL connexion.
 *                 sslmode?: scalar|null, // Determines whether or with what priority a SSL TCP/IP connection will be negotiated with the server for PostgreSQL.
 *                 sslrootcert?: scalar|null, // The name of a file containing SSL certificate authority (CA) certificate(s). If the file exists, the server's certificate will be verified to be signed by one of these authorities.
 *                 sslcert?: scalar|null, // The path to the SSL client certificate file for PostgreSQL.
 *                 sslkey?: scalar|null, // The path to the SSL client key file for PostgreSQL.
 *                 sslcrl?: scalar|null, // The file name of the SSL certificate revocation list for PostgreSQL.
 *                 pooled?: bool, // True to use a pooled server with the oci8/pdo_oracle driver
 *                 MultipleActiveResultSets?: bool, // Configuring MultipleActiveResultSets for the pdo_sqlsrv driver
 *                 use_savepoints?: bool, // Use savepoints for nested transactions
 *                 instancename?: scalar|null, // Optional parameter, complete whether to add the INSTANCE_NAME parameter in the connection. It is generally used to connect to an Oracle RAC server to select the name of a particular instance.
 *                 connectstring?: scalar|null, // Complete Easy Connect connection descriptor, see https://docs.oracle.com/database/121/NETAG/naming.htm.When using this option, you will still need to provide the user and password parameters, but the other parameters will no longer be used. Note that when using this parameter, the getHost and getPort methods from Doctrine\DBAL\Connection will no longer function as expected.
 *             }>,
 *             replicas?: array<string, array{ // Default: []
 *                 url?: scalar|null, // A URL with connection information; any parameter value parsed from this string will override explicitly set parameters
 *                 dbname?: scalar|null,
 *                 host?: scalar|null, // Defaults to "localhost" at runtime.
 *                 port?: scalar|null, // Defaults to null at runtime.
 *                 user?: scalar|null, // Defaults to "root" at runtime.
 *                 password?: scalar|null, // Defaults to null at runtime.
 *                 override_url?: bool, // Deprecated: The "doctrine.dbal.override_url" configuration key is deprecated.
 *                 dbname_suffix?: scalar|null, // Adds the given suffix to the configured database name, this option has no effects for the SQLite platform
 *                 application_name?: scalar|null,
 *                 charset?: scalar|null,
 *                 path?: scalar|null,
 *                 memory?: bool,
 *                 unix_socket?: scalar|null, // The unix socket to use for MySQL
 *                 persistent?: bool, // True to use as persistent connection for the ibm_db2 driver
 *                 protocol?: scalar|null, // The protocol to use for the ibm_db2 driver (default to TCPIP if omitted)
 *                 service?: bool, // True to use SERVICE_NAME as connection parameter instead of SID for Oracle
 *                 servicename?: scalar|null, // Overrules dbname parameter if given and used as SERVICE_NAME or SID connection parameter for Oracle depending on the service parameter.
 *                 sessionMode?: scalar|null, // The session mode to use for the oci8 driver
 *                 server?: scalar|null, // The name of a running database server to connect to for SQL Anywhere.
 *                 default_dbname?: scalar|null, // Override the default database (postgres) to connect to for PostgreSQL connexion.
 *                 sslmode?: scalar|null, // Determines whether or with what priority a SSL TCP/IP connection will be negotiated with the server for PostgreSQL.
 *                 sslrootcert?: scalar|null, // The name of a file containing SSL certificate authority (CA) certificate(s). If the file exists, the server's certificate will be verified to be signed by one of these authorities.
 *                 sslcert?: scalar|null, // The path to the SSL client certificate file for PostgreSQL.
 *                 sslkey?: scalar|null, // The path to the SSL client key file for PostgreSQL.
 *                 sslcrl?: scalar|null, // The file name of the SSL certificate revocation list for PostgreSQL.
 *                 pooled?: bool, // True to use a pooled server with the oci8/pdo_oracle driver
 *                 MultipleActiveResultSets?: bool, // Configuring MultipleActiveResultSets for the pdo_sqlsrv driver
 *                 use_savepoints?: bool, // Use savepoints for nested transactions
 *                 instancename?: scalar|null, // Optional parameter, complete whether to add the INSTANCE_NAME parameter in the connection. It is generally used to connect to an Oracle RAC server to select the name of a particular instance.
 *                 connectstring?: scalar|null, // Complete Easy Connect connection descriptor, see https://docs.oracle.com/database/121/NETAG/naming.htm.When using this option, you will still need to provide the user and password parameters, but the other parameters will no longer be used. Note that when using this parameter, the getHost and getPort methods from Doctrine\DBAL\Connection will no longer function as expected.
 *             }>,
 *         }>,
 *     },
 *     orm?: array{
 *         default_entity_manager?: scalar|null,
 *         auto_generate_proxy_classes?: scalar|null, // Auto generate mode possible values are: "NEVER", "ALWAYS", "FILE_NOT_EXISTS", "EVAL", "FILE_NOT_EXISTS_OR_CHANGED", this option is ignored when the "enable_native_lazy_objects" option is true // Default: false
 *         enable_lazy_ghost_objects?: bool, // Enables the new implementation of proxies based on lazy ghosts instead of using the legacy implementation // Default: true
 *         enable_native_lazy_objects?: bool, // Enables the new native implementation of PHP lazy objects instead of generated proxies // Default: false
 *         proxy_dir?: scalar|null, // Configures the path where generated proxy classes are saved when using non-native lazy objects, this option is ignored when the "enable_native_lazy_objects" option is true // Default: "%kernel.build_dir%/doctrine/orm/Proxies"
 *         proxy_namespace?: scalar|null, // Defines the root namespace for generated proxy classes when using non-native lazy objects, this option is ignored when the "enable_native_lazy_objects" option is true // Default: "Proxies"
 *         controller_resolver?: bool|array{
 *             enabled?: bool, // Default: true
 *             auto_mapping?: bool|null, // Set to false to disable using route placeholders as lookup criteria when the primary key doesn't match the argument name // Default: null
 *             evict_cache?: bool, // Set to true to fetch the entity from the database instead of using the cache, if any // Default: false
 *         },
 *         entity_managers?: array<string, array{ // Default: []
 *             query_cache_driver?: string|array{
 *                 type?: scalar|null, // Default: null
 *                 id?: scalar|null,
 *                 pool?: scalar|null,
 *             },
 *             metadata_cache_driver?: string|array{
 *                 type?: scalar|null, // Default: null
 *                 id?: scalar|null,
 *                 pool?: scalar|null,
 *             },
 *             result_cache_driver?: string|array{
 *                 type?: scalar|null, // Default: null
 *                 id?: scalar|null,
 *                 pool?: scalar|null,
 *             },
 *             entity_listeners?: array{
 *                 entities?: array<string, array{ // Default: []
 *                     listeners?: array<string, array{ // Default: []
 *                         events?: list<array{ // Default: []
 *                             type?: scalar|null,
 *                             method?: scalar|null, // Default: null
 *                         }>,
 *                     }>,
 *                 }>,
 *             },
 *             connection?: scalar|null,
 *             class_metadata_factory_name?: scalar|null, // Default: "Doctrine\\ORM\\Mapping\\ClassMetadataFactory"
 *             default_repository_class?: scalar|null, // Default: "Doctrine\\ORM\\EntityRepository"
 *             auto_mapping?: scalar|null, // Default: false
 *             naming_strategy?: scalar|null, // Default: "doctrine.orm.naming_strategy.default"
 *             quote_strategy?: scalar|null, // Default: "doctrine.orm.quote_strategy.default"
 *             typed_field_mapper?: scalar|null, // Default: "doctrine.orm.typed_field_mapper.default"
 *             entity_listener_resolver?: scalar|null, // Default: null
 *             fetch_mode_subselect_batch_size?: scalar|null,
 *             repository_factory?: scalar|null, // Default: "doctrine.orm.container_repository_factory"
 *             schema_ignore_classes?: list<scalar|null>,
 *             report_fields_where_declared?: bool, // Set to "true" to opt-in to the new mapping driver mode that was added in Doctrine ORM 2.16 and will be mandatory in ORM 3.0. See https://github.com/doctrine/orm/pull/10455. // Default: true
 *             validate_xml_mapping?: bool, // Set to "true" to opt-in to the new mapping driver mode that was added in Doctrine ORM 2.14 and will be mandatory in ORM 3.0. See https://github.com/doctrine/orm/pull/6728. // Default: false
 *             second_level_cache?: array{
 *                 region_cache_driver?: string|array{
 *                     type?: scalar|null, // Default: null
 *                     id?: scalar|null,
 *                     pool?: scalar|null,
 *                 },
 *                 region_lock_lifetime?: scalar|null, // Default: 60
 *                 log_enabled?: bool, // Default: true
 *                 region_lifetime?: scalar|null, // Default: 3600
 *                 enabled?: bool, // Default: true
 *                 factory?: scalar|null,
 *                 regions?: array<string, array{ // Default: []
 *                     cache_driver?: string|array{
 *                         type?: scalar|null, // Default: null
 *                         id?: scalar|null,
 *                         pool?: scalar|null,
 *                     },
 *                     lock_path?: scalar|null, // Default: "%kernel.cache_dir%/doctrine/orm/slc/filelock"
 *                     lock_lifetime?: scalar|null, // Default: 60
 *                     type?: scalar|null, // Default: "default"
 *                     lifetime?: scalar|null, // Default: 0
 *                     service?: scalar|null,
 *                     name?: scalar|null,
 *                 }>,
 *                 loggers?: array<string, array{ // Default: []
 *                     name?: scalar|null,
 *                     service?: scalar|null,
 *                 }>,
 *             },
 *             hydrators?: array<string, scalar|null>,
 *             mappings?: array<string, bool|string|array{ // Default: []
 *                 mapping?: scalar|null, // Default: true
 *                 type?: scalar|null,
 *                 dir?: scalar|null,
 *                 alias?: scalar|null,
 *                 prefix?: scalar|null,
 *                 is_bundle?: bool,
 *             }>,
 *             dql?: array{
 *                 string_functions?: array<string, scalar|null>,
 *                 numeric_functions?: array<string, scalar|null>,
 *                 datetime_functions?: array<string, scalar|null>,
 *             },
 *             filters?: array<string, string|array{ // Default: []
 *                 class: scalar|null,
 *                 enabled?: bool, // Default: false
 *                 parameters?: array<string, mixed>,
 *             }>,
 *             identity_generation_preferences?: array<string, scalar|null>,
 *         }>,
 *         resolve_target_entities?: array<string, scalar|null>,
 *     },
 * }
 * @psalm-type DoctrineMigrationsConfig = array{
 *     enable_service_migrations?: bool, // Whether to enable fetching migrations from the service container. // Default: false
 *     migrations_paths?: array<string, scalar|null>,
 *     services?: array<string, scalar|null>,
 *     factories?: array<string, scalar|null>,
 *     storage?: array{ // Storage to use for migration status metadata.
 *         table_storage?: array{ // The default metadata storage, implemented as a table in the database.
 *             table_name?: scalar|null, // Default: null
 *             version_column_name?: scalar|null, // Default: null
 *             version_column_length?: scalar|null, // Default: null
 *             executed_at_column_name?: scalar|null, // Default: null
 *             execution_time_column_name?: scalar|null, // Default: null
 *         },
 *     },
 *     migrations?: list<scalar|null>,
 *     connection?: scalar|null, // Connection name to use for the migrations database. // Default: null
 *     em?: scalar|null, // Entity manager name to use for the migrations database (available when doctrine/orm is installed). // Default: null
 *     all_or_nothing?: scalar|null, // Run all migrations in a transaction. // Default: false
 *     check_database_platform?: scalar|null, // Adds an extra check in the generated migrations to allow execution only on the same platform as they were initially generated on. // Default: true
 *     custom_template?: scalar|null, // Custom template path for generated migration classes. // Default: null
 *     organize_migrations?: scalar|null, // Organize migrations mode. Possible values are: "BY_YEAR", "BY_YEAR_AND_MONTH", false // Default: false
 *     enable_profiler?: bool, // Whether or not to enable the profiler collector to calculate and visualize migration status. This adds some queries overhead. // Default: false
 *     transactional?: bool, // Whether or not to wrap migrations in a single transaction. // Default: true
 * }
 * @psalm-type EightPointsGuzzleConfig = array{
 *     clients?: array<string, array{ // Default: []
 *         class?: scalar|null, // Default: "%eight_points_guzzle.http_client.class%"
 *         base_url?: scalar|null, // Default: null
 *         lazy?: bool, // Default: false
 *         logging?: int, // Default: null
 *         handler?: scalar|null, // Default: null
 *         options?: array{
 *             headers?: array<string, scalar|null>,
 *             allow_redirects?: mixed,
 *             auth?: mixed,
 *             query?: mixed,
 *             curl?: list<scalar|null>,
 *             cert?: mixed,
 *             connect_timeout?: scalar|null,
 *             debug?: bool,
 *             decode_content?: mixed,
 *             delay?: float,
 *             form_params?: array<string, mixed>,
 *             multipart?: list<mixed>,
 *             sink?: scalar|null,
 *             http_errors?: bool,
 *             expect?: mixed,
 *             ssl_key?: mixed,
 *             stream?: bool,
 *             synchronous?: bool,
 *             read_timeout?: scalar|null,
 *             timeout?: scalar|null,
 *             verify?: mixed,
 *             cookies?: bool,
 *             proxy?: string|array{
 *                 http?: scalar|null,
 *                 https?: scalar|null,
 *                 no?: list<scalar|null>,
 *             },
 *             version?: scalar|null,
 *         },
 *         plugin?: array<mixed>,
 *     }>,
 *     logging?: bool, // Default: true
 *     profiling?: bool, // Default: true
 *     slow_response_time?: int, // Default: 0
 * }
 * @psalm-type KnpMenuConfig = array{
 *     providers?: array{
 *         builder_alias?: bool, // Default: true
 *     },
 *     twig?: array{
 *         template?: scalar|null, // Default: "@KnpMenu/menu.html.twig"
 *     },
 *     templating?: bool, // Default: false
 *     default_renderer?: scalar|null, // Default: "twig"
 * }
 * @psalm-type SonataBlockConfig = array{
 *     profiler?: array{
 *         enabled?: scalar|null, // Default: "%kernel.debug%"
 *         template?: scalar|null, // Default: "@SonataBlock/Profiler/block.html.twig"
 *     },
 *     default_contexts?: list<scalar|null>,
 *     context_manager?: scalar|null, // Default: "sonata.block.context_manager.default"
 *     http_cache?: bool, // Deprecated: The "http_cache" option is deprecated and not doing anything anymore since sonata-project/block-bundle 5.0. It will be removed in 6.0. // Default: false
 *     templates?: array{
 *         block_base?: scalar|null, // Default: null
 *         block_container?: scalar|null, // Default: null
 *     },
 *     container?: array{ // block container configuration
 *         types?: list<scalar|null>,
 *         templates?: list<scalar|null>,
 *     },
 *     blocks?: array<string, array{ // Default: []
 *         contexts?: list<scalar|null>,
 *         templates?: list<array{ // Default: []
 *             name?: scalar|null,
 *             template?: scalar|null,
 *         }>,
 *         settings?: array<string, scalar|null>,
 *         exception?: array{
 *             filter?: scalar|null, // Default: null
 *             renderer?: scalar|null, // Default: null
 *         },
 *     }>,
 *     blocks_by_class?: array<string, array{ // Default: []
 *         settings?: array<string, scalar|null>,
 *     }>,
 *     exception?: array{
 *         default?: array{
 *             filter?: scalar|null, // Default: "debug_only"
 *             renderer?: scalar|null, // Default: "throw"
 *         },
 *         filters?: array<string, scalar|null>,
 *         renderers?: array<string, scalar|null>,
 *     },
 * }
 * @psalm-type SonataAdminConfig = array{
 *     security?: array{
 *         handler?: scalar|null, // Default: "sonata.admin.security.handler.noop"
 *         information?: array<string, string|list<scalar|null>>,
 *         admin_permissions?: list<scalar|null>,
 *         role_admin?: scalar|null, // Role which will see the top nav bar and dropdown groups regardless of its configuration // Default: "ROLE_SONATA_ADMIN"
 *         role_super_admin?: scalar|null, // Role which will perform all admin actions, see dashboard, menu and search groups regardless of its configuration // Default: "ROLE_SUPER_ADMIN"
 *         object_permissions?: list<scalar|null>,
 *         acl_user_manager?: scalar|null, // Default: null
 *     },
 *     title?: scalar|null, // Default: "Sonata Admin"
 *     title_logo?: scalar|null, // Default: "bundles/sonataadmin/images/logo_title.png"
 *     search?: bool, // Enable/disable the search form in the sidebar // Default: true
 *     global_search?: array{
 *         empty_boxes?: scalar|null, // Perhaps one of the three options: show, fade, hide. // Default: "show"
 *         admin_route?: scalar|null, // Change the default route used to generate the link to the object // Default: "show"
 *     },
 *     default_controller?: scalar|null, // Name of the controller class to be used as a default in admin definitions // Default: "sonata.admin.controller.crud"
 *     breadcrumbs?: array{
 *         child_admin_route?: scalar|null, // Change the default route used to generate the link to the parent object, when in a child admin // Default: "show"
 *     },
 *     options?: array{
 *         html5_validate?: bool, // Default: true
 *         sort_admins?: bool, // Auto order groups and admins by label or id // Default: false
 *         confirm_exit?: bool, // Default: true
 *         js_debug?: bool, // Default: false
 *         skin?: "skin-black"|"skin-black-light"|"skin-blue"|"skin-blue-light"|"skin-green"|"skin-green-light"|"skin-purple"|"skin-purple-light"|"skin-red"|"skin-red-light"|"skin-yellow"|"skin-yellow-light", // Default: "skin-black"
 *         use_select2?: bool, // Default: true
 *         use_icheck?: bool, // Default: true
 *         use_bootlint?: bool, // Default: false
 *         use_stickyforms?: bool, // Default: true
 *         pager_links?: int, // Default: null
 *         form_type?: scalar|null, // Default: "standard"
 *         default_admin_route?: scalar|null, // Name of the admin route to be used as a default to generate the link to the object // Default: "show"
 *         default_group?: scalar|null, // Group used for admin services if one isn't provided. // Default: "default"
 *         default_label_catalogue?: scalar|null, // Deprecated: The "default_label_catalogue" node is deprecated, use "default_translation_domain" instead. // Label Catalogue used for admin services if one isn't provided. // Default: "SonataAdminBundle"
 *         default_translation_domain?: scalar|null, // Translation domain used for admin services if one isn't provided. // Default: null
 *         default_icon?: scalar|null, // Icon used for admin services if one isn't provided. // Default: "fas fa-folder"
 *         dropdown_number_groups_per_colums?: int, // Default: 2
 *         logo_content?: "text"|"icon"|"all", // Default: "all"
 *         list_action_button_content?: "text"|"icon"|"all", // Default: "all"
 *         lock_protection?: bool, // Enable locking when editing an object, if the corresponding object manager supports it. // Default: false
 *         mosaic_background?: scalar|null, // Background used in mosaic view // Default: "bundles/sonataadmin/images/default_mosaic_image.png"
 *     },
 *     dashboard?: array{
 *         groups?: array<string, array{ // Default: []
 *             label?: scalar|null,
 *             translation_domain?: scalar|null,
 *             label_catalogue?: scalar|null, // Deprecated: The "label_catalogue" node is deprecated, use "translation_domain" instead.
 *             icon?: scalar|null,
 *             on_top?: scalar|null, // Show menu item in side dashboard menu without treeview // Default: false
 *             keep_open?: scalar|null, // Keep menu group always open // Default: false
 *             provider?: scalar|null,
 *             items?: list<array{ // Default: []
 *                 admin?: scalar|null,
 *                 label?: scalar|null,
 *                 route?: scalar|null,
 *                 roles?: list<scalar|null>,
 *                 route_params?: list<scalar|null>,
 *                 route_absolute?: bool, // Whether the generated url should be absolute // Default: false
 *             }>,
 *             item_adds?: list<scalar|null>,
 *             roles?: list<scalar|null>,
 *         }>,
 *         blocks?: list<array{ // Default: [{"position":"left","settings":[],"type":"sonata.admin.block.admin_list","roles":[]}]
 *             type?: scalar|null,
 *             roles?: list<scalar|null>,
 *             settings?: array<string, mixed>,
 *             position?: scalar|null, // Default: "right"
 *             class?: scalar|null, // Default: "col-md-4"
 *         }>,
 *     },
 *     default_admin_services?: array{
 *         model_manager?: scalar|null, // Default: null
 *         data_source?: scalar|null, // Default: null
 *         field_description_factory?: scalar|null, // Default: null
 *         form_contractor?: scalar|null, // Default: null
 *         show_builder?: scalar|null, // Default: null
 *         list_builder?: scalar|null, // Default: null
 *         datagrid_builder?: scalar|null, // Default: null
 *         translator?: scalar|null, // Default: null
 *         configuration_pool?: scalar|null, // Default: null
 *         route_generator?: scalar|null, // Default: null
 *         security_handler?: scalar|null, // Default: null
 *         menu_factory?: scalar|null, // Default: null
 *         route_builder?: scalar|null, // Default: null
 *         label_translator_strategy?: scalar|null, // Default: null
 *         pager_type?: scalar|null, // Default: null
 *     },
 *     templates?: array{
 *         user_block?: scalar|null, // Default: "@SonataAdmin/Core/user_block.html.twig"
 *         add_block?: scalar|null, // Default: "@SonataAdmin/Core/add_block.html.twig"
 *         layout?: scalar|null, // Default: "@SonataAdmin/standard_layout.html.twig"
 *         ajax?: scalar|null, // Default: "@SonataAdmin/ajax_layout.html.twig"
 *         dashboard?: scalar|null, // Default: "@SonataAdmin/Core/dashboard.html.twig"
 *         search?: scalar|null, // Default: "@SonataAdmin/Core/search.html.twig"
 *         list?: scalar|null, // Default: "@SonataAdmin/CRUD/list.html.twig"
 *         filter?: scalar|null, // Default: "@SonataAdmin/Form/filter_admin_fields.html.twig"
 *         show?: scalar|null, // Default: "@SonataAdmin/CRUD/show.html.twig"
 *         show_compare?: scalar|null, // Default: "@SonataAdmin/CRUD/show_compare.html.twig"
 *         edit?: scalar|null, // Default: "@SonataAdmin/CRUD/edit.html.twig"
 *         preview?: scalar|null, // Default: "@SonataAdmin/CRUD/preview.html.twig"
 *         history?: scalar|null, // Default: "@SonataAdmin/CRUD/history.html.twig"
 *         acl?: scalar|null, // Default: "@SonataAdmin/CRUD/acl.html.twig"
 *         history_revision_timestamp?: scalar|null, // Default: "@SonataAdmin/CRUD/history_revision_timestamp.html.twig"
 *         action?: scalar|null, // Default: "@SonataAdmin/CRUD/action.html.twig"
 *         select?: scalar|null, // Default: "@SonataAdmin/CRUD/list__select.html.twig"
 *         list_block?: scalar|null, // Default: "@SonataAdmin/Block/block_admin_list.html.twig"
 *         search_result_block?: scalar|null, // Default: "@SonataAdmin/Block/block_search_result.html.twig"
 *         short_object_description?: scalar|null, // Default: "@SonataAdmin/Helper/short-object-description.html.twig"
 *         delete?: scalar|null, // Default: "@SonataAdmin/CRUD/delete.html.twig"
 *         batch?: scalar|null, // Default: "@SonataAdmin/CRUD/list__batch.html.twig"
 *         batch_confirmation?: scalar|null, // Default: "@SonataAdmin/CRUD/batch_confirmation.html.twig"
 *         inner_list_row?: scalar|null, // Default: "@SonataAdmin/CRUD/list_inner_row.html.twig"
 *         outer_list_rows_mosaic?: scalar|null, // Default: "@SonataAdmin/CRUD/list_outer_rows_mosaic.html.twig"
 *         outer_list_rows_list?: scalar|null, // Default: "@SonataAdmin/CRUD/list_outer_rows_list.html.twig"
 *         outer_list_rows_tree?: scalar|null, // Default: "@SonataAdmin/CRUD/list_outer_rows_tree.html.twig"
 *         base_list_field?: scalar|null, // Default: "@SonataAdmin/CRUD/base_list_field.html.twig"
 *         pager_links?: scalar|null, // Default: "@SonataAdmin/Pager/links.html.twig"
 *         pager_results?: scalar|null, // Default: "@SonataAdmin/Pager/results.html.twig"
 *         tab_menu_template?: scalar|null, // Default: "@SonataAdmin/Core/tab_menu_template.html.twig"
 *         knp_menu_template?: scalar|null, // Default: "@SonataAdmin/Menu/sonata_menu.html.twig"
 *         action_create?: scalar|null, // Default: "@SonataAdmin/CRUD/dashboard__action_create.html.twig"
 *         button_acl?: scalar|null, // Default: "@SonataAdmin/Button/acl_button.html.twig"
 *         button_create?: scalar|null, // Default: "@SonataAdmin/Button/create_button.html.twig"
 *         button_edit?: scalar|null, // Default: "@SonataAdmin/Button/edit_button.html.twig"
 *         button_history?: scalar|null, // Default: "@SonataAdmin/Button/history_button.html.twig"
 *         button_list?: scalar|null, // Default: "@SonataAdmin/Button/list_button.html.twig"
 *         button_show?: scalar|null, // Default: "@SonataAdmin/Button/show_button.html.twig"
 *         form_theme?: list<scalar|null>,
 *         filter_theme?: list<scalar|null>,
 *     },
 *     assets?: array{
 *         stylesheets?: list<array{ // Default: [{"path":"bundles/sonataadmin/app.css","package_name":"sonata_admin"},{"path":"bundles/sonataform/app.css","package_name":"sonata_admin"}]
 *             path: scalar|null,
 *             package_name?: scalar|null, // Default: "sonata_admin"
 *         }>,
 *         extra_stylesheets?: list<array{ // Default: []
 *             path: scalar|null,
 *             package_name?: scalar|null, // Default: "sonata_admin"
 *         }>,
 *         remove_stylesheets?: list<array{ // Default: []
 *             path: scalar|null,
 *             package_name?: scalar|null, // Default: "sonata_admin"
 *         }>,
 *         javascripts?: list<array{ // Default: [{"path":"bundles/sonataadmin/app.js","package_name":"sonata_admin"},{"path":"bundles/sonataform/app.js","package_name":"sonata_admin"}]
 *             path: scalar|null,
 *             package_name?: scalar|null, // Default: "sonata_admin"
 *         }>,
 *         extra_javascripts?: list<array{ // Default: []
 *             path: scalar|null,
 *             package_name?: scalar|null, // Default: "sonata_admin"
 *         }>,
 *         remove_javascripts?: list<array{ // Default: []
 *             path: scalar|null,
 *             package_name?: scalar|null, // Default: "sonata_admin"
 *         }>,
 *     },
 *     extensions?: array<string, array{ // Default: []
 *         global?: bool, // Default: false
 *         admins?: list<scalar|null>,
 *         excludes?: list<scalar|null>,
 *         implements?: list<scalar|null>,
 *         extends?: list<scalar|null>,
 *         instanceof?: list<scalar|null>,
 *         uses?: list<scalar|null>,
 *         admin_implements?: list<scalar|null>,
 *         admin_extends?: list<scalar|null>,
 *         admin_instanceof?: list<scalar|null>,
 *         admin_uses?: list<scalar|null>,
 *         priority?: int, // Positive or negative integer. The higher the priority, the earlier it’s executed. // Default: 0
 *     }>,
 *     persist_filters?: scalar|null, // Default: false
 *     filter_persister?: scalar|null, // Default: "sonata.admin.filter_persister.session"
 *     show_mosaic_button?: bool, // Show mosaic button on all admin screens // Default: true
 * }
 * @psalm-type SonataDoctrineOrmAdminConfig = array{
 *     entity_manager?: scalar|null, // Default: null
 *     audit?: array{
 *         force?: bool, // Default: true
 *     },
 *     templates?: array{
 *         types?: array{
 *             list?: array<string, scalar|null>,
 *             show?: array<string, scalar|null>,
 *         },
 *     },
 * }
 * @psalm-type SonataExporterConfig = array{
 *     exporter?: array{
 *         default_writers?: list<scalar|null>,
 *     },
 *     writers?: array{
 *         csv?: array{
 *             filename?: scalar|null, // path to the output file // Default: "php://output"
 *             delimiter?: scalar|null, // delimits csv values // Default: ","
 *             enclosure?: scalar|null, // will be used when a value contains the delimiter // Default: "\""
 *             escape?: scalar|null, // will be used when a value contains the enclosure // Default: "\\"
 *             show_headers?: bool, // add column names as the first line // Default: true
 *             with_bom?: bool, // include the byte order mark // Default: false
 *         },
 *         json?: array{
 *             filename?: scalar|null, // path to the output file // Default: "php://output"
 *         },
 *         xls?: array{
 *             filename?: scalar|null, // path to the output file // Default: "php://output"
 *             show_headers?: bool, // add column names as the first line // Default: true
 *         },
 *         xlsx?: array{
 *             filename?: scalar|null, // path to the output file // Default: "php://output"
 *             show_headers?: bool, // add column names as the first line // Default: true
 *             show_filters?: bool, // add filters in the first line // Default: true
 *         },
 *         xml?: array{
 *             filename?: scalar|null, // path to the output file // Default: "php://output"
 *             show_headers?: bool, // add column names as the first line // Default: true
 *             main_element?: scalar|null, // name of the wrapping element // Default: "datas"
 *             child_element?: scalar|null, // name of elements corresponding to rows // Default: "data"
 *         },
 *     },
 * }
 * @psalm-type MonologConfig = array{
 *     use_microseconds?: scalar|null, // Default: true
 *     channels?: list<scalar|null>,
 *     handlers?: array<string, array{ // Default: []
 *         type: scalar|null,
 *         id?: scalar|null,
 *         enabled?: bool, // Default: true
 *         priority?: scalar|null, // Default: 0
 *         level?: scalar|null, // Default: "DEBUG"
 *         bubble?: bool, // Default: true
 *         interactive_only?: bool, // Default: false
 *         app_name?: scalar|null, // Default: null
 *         include_stacktraces?: bool, // Default: false
 *         process_psr_3_messages?: array{
 *             enabled?: bool|null, // Default: null
 *             date_format?: scalar|null,
 *             remove_used_context_fields?: bool,
 *         },
 *         path?: scalar|null, // Default: "%kernel.logs_dir%/%kernel.environment%.log"
 *         file_permission?: scalar|null, // Default: null
 *         use_locking?: bool, // Default: false
 *         filename_format?: scalar|null, // Default: "{filename}-{date}"
 *         date_format?: scalar|null, // Default: "Y-m-d"
 *         ident?: scalar|null, // Default: false
 *         logopts?: scalar|null, // Default: 1
 *         facility?: scalar|null, // Default: "user"
 *         max_files?: scalar|null, // Default: 0
 *         action_level?: scalar|null, // Default: "WARNING"
 *         activation_strategy?: scalar|null, // Default: null
 *         stop_buffering?: bool, // Default: true
 *         passthru_level?: scalar|null, // Default: null
 *         excluded_http_codes?: list<array{ // Default: []
 *             code?: scalar|null,
 *             urls?: list<scalar|null>,
 *         }>,
 *         accepted_levels?: list<scalar|null>,
 *         min_level?: scalar|null, // Default: "DEBUG"
 *         max_level?: scalar|null, // Default: "EMERGENCY"
 *         buffer_size?: scalar|null, // Default: 0
 *         flush_on_overflow?: bool, // Default: false
 *         handler?: scalar|null,
 *         url?: scalar|null,
 *         exchange?: scalar|null,
 *         exchange_name?: scalar|null, // Default: "log"
 *         channel?: scalar|null, // Default: null
 *         bot_name?: scalar|null, // Default: "Monolog"
 *         use_attachment?: scalar|null, // Default: true
 *         use_short_attachment?: scalar|null, // Default: false
 *         include_extra?: scalar|null, // Default: false
 *         icon_emoji?: scalar|null, // Default: null
 *         webhook_url?: scalar|null,
 *         exclude_fields?: list<scalar|null>,
 *         token?: scalar|null,
 *         region?: scalar|null,
 *         source?: scalar|null,
 *         use_ssl?: bool, // Default: true
 *         user?: mixed,
 *         title?: scalar|null, // Default: null
 *         host?: scalar|null, // Default: null
 *         port?: scalar|null, // Default: 514
 *         config?: list<scalar|null>,
 *         members?: list<scalar|null>,
 *         connection_string?: scalar|null,
 *         timeout?: scalar|null,
 *         time?: scalar|null, // Default: 60
 *         deduplication_level?: scalar|null, // Default: 400
 *         store?: scalar|null, // Default: null
 *         connection_timeout?: scalar|null,
 *         persistent?: bool,
 *         message_type?: scalar|null, // Default: 0
 *         parse_mode?: scalar|null, // Default: null
 *         disable_webpage_preview?: bool|null, // Default: null
 *         disable_notification?: bool|null, // Default: null
 *         split_long_messages?: bool, // Default: false
 *         delay_between_messages?: bool, // Default: false
 *         topic?: int, // Default: null
 *         factor?: int, // Default: 1
 *         tags?: list<scalar|null>,
 *         console_formatter_options?: mixed, // Default: []
 *         formatter?: scalar|null,
 *         nested?: bool, // Default: false
 *         publisher?: string|array{
 *             id?: scalar|null,
 *             hostname?: scalar|null,
 *             port?: scalar|null, // Default: 12201
 *             chunk_size?: scalar|null, // Default: 1420
 *             encoder?: "json"|"compressed_json",
 *         },
 *         mongodb?: string|array{
 *             id?: scalar|null, // ID of a MongoDB\Client service
 *             uri?: scalar|null,
 *             username?: scalar|null,
 *             password?: scalar|null,
 *             database?: scalar|null, // Default: "monolog"
 *             collection?: scalar|null, // Default: "logs"
 *         },
 *         elasticsearch?: string|array{
 *             id?: scalar|null,
 *             hosts?: list<scalar|null>,
 *             host?: scalar|null,
 *             port?: scalar|null, // Default: 9200
 *             transport?: scalar|null, // Default: "Http"
 *             user?: scalar|null, // Default: null
 *             password?: scalar|null, // Default: null
 *         },
 *         index?: scalar|null, // Default: "monolog"
 *         document_type?: scalar|null, // Default: "logs"
 *         ignore_error?: scalar|null, // Default: false
 *         redis?: string|array{
 *             id?: scalar|null,
 *             host?: scalar|null,
 *             password?: scalar|null, // Default: null
 *             port?: scalar|null, // Default: 6379
 *             database?: scalar|null, // Default: 0
 *             key_name?: scalar|null, // Default: "monolog_redis"
 *         },
 *         predis?: string|array{
 *             id?: scalar|null,
 *             host?: scalar|null,
 *         },
 *         from_email?: scalar|null,
 *         to_email?: list<scalar|null>,
 *         subject?: scalar|null,
 *         content_type?: scalar|null, // Default: null
 *         headers?: list<scalar|null>,
 *         mailer?: scalar|null, // Default: null
 *         email_prototype?: string|array{
 *             id: scalar|null,
 *             method?: scalar|null, // Default: null
 *         },
 *         verbosity_levels?: array{
 *             VERBOSITY_QUIET?: scalar|null, // Default: "ERROR"
 *             VERBOSITY_NORMAL?: scalar|null, // Default: "WARNING"
 *             VERBOSITY_VERBOSE?: scalar|null, // Default: "NOTICE"
 *             VERBOSITY_VERY_VERBOSE?: scalar|null, // Default: "INFO"
 *             VERBOSITY_DEBUG?: scalar|null, // Default: "DEBUG"
 *         },
 *         channels?: string|array{
 *             type?: scalar|null,
 *             elements?: list<scalar|null>,
 *         },
 *     }>,
 * }
 * @psalm-type MakerConfig = array{
 *     root_namespace?: scalar|null, // Default: "App"
 *     generate_final_classes?: bool, // Default: true
 *     generate_final_entities?: bool, // Default: false
 * }
 * @psalm-type SecurityConfig = array{
 *     access_denied_url?: scalar|null, // Default: null
 *     session_fixation_strategy?: "none"|"migrate"|"invalidate", // Default: "migrate"
 *     hide_user_not_found?: bool, // Deprecated: The "hide_user_not_found" option is deprecated and will be removed in 8.0. Use the "expose_security_errors" option instead.
 *     expose_security_errors?: \Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::None|\Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::AccountStatus|\Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::All, // Default: "none"
 *     erase_credentials?: bool, // Default: true
 *     access_decision_manager?: array{
 *         strategy?: "affirmative"|"consensus"|"unanimous"|"priority",
 *         service?: scalar|null,
 *         strategy_service?: scalar|null,
 *         allow_if_all_abstain?: bool, // Default: false
 *         allow_if_equal_granted_denied?: bool, // Default: true
 *     },
 *     password_hashers?: array<string, string|array{ // Default: []
 *         algorithm?: scalar|null,
 *         migrate_from?: list<scalar|null>,
 *         hash_algorithm?: scalar|null, // Name of hashing algorithm for PBKDF2 (i.e. sha256, sha512, etc..) See hash_algos() for a list of supported algorithms. // Default: "sha512"
 *         key_length?: scalar|null, // Default: 40
 *         ignore_case?: bool, // Default: false
 *         encode_as_base64?: bool, // Default: true
 *         iterations?: scalar|null, // Default: 5000
 *         cost?: int, // Default: null
 *         memory_cost?: scalar|null, // Default: null
 *         time_cost?: scalar|null, // Default: null
 *         id?: scalar|null,
 *     }>,
 *     providers?: array<string, array{ // Default: []
 *         id?: scalar|null,
 *         chain?: array{
 *             providers?: list<scalar|null>,
 *         },
 *         entity?: array{
 *             class: scalar|null, // The full entity class name of your user class.
 *             property?: scalar|null, // Default: null
 *             manager_name?: scalar|null, // Default: null
 *         },
 *         memory?: array{
 *             users?: array<string, array{ // Default: []
 *                 password?: scalar|null, // Default: null
 *                 roles?: list<scalar|null>,
 *             }>,
 *         },
 *         ldap?: array{
 *             service: scalar|null,
 *             base_dn: scalar|null,
 *             search_dn?: scalar|null, // Default: null
 *             search_password?: scalar|null, // Default: null
 *             extra_fields?: list<scalar|null>,
 *             default_roles?: list<scalar|null>,
 *             role_fetcher?: scalar|null, // Default: null
 *             uid_key?: scalar|null, // Default: "sAMAccountName"
 *             filter?: scalar|null, // Default: "({uid_key}={user_identifier})"
 *             password_attribute?: scalar|null, // Default: null
 *         },
 *         lexik_jwt?: array{
 *             class?: scalar|null, // Default: "Lexik\\Bundle\\JWTAuthenticationBundle\\Security\\User\\JWTUser"
 *         },
 *     }>,
 *     firewalls: array<string, array{ // Default: []
 *         pattern?: scalar|null,
 *         host?: scalar|null,
 *         methods?: list<scalar|null>,
 *         security?: bool, // Default: true
 *         user_checker?: scalar|null, // The UserChecker to use when authenticating users in this firewall. // Default: "security.user_checker"
 *         request_matcher?: scalar|null,
 *         access_denied_url?: scalar|null,
 *         access_denied_handler?: scalar|null,
 *         entry_point?: scalar|null, // An enabled authenticator name or a service id that implements "Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface".
 *         provider?: scalar|null,
 *         stateless?: bool, // Default: false
 *         lazy?: bool, // Default: false
 *         context?: scalar|null,
 *         logout?: array{
 *             enable_csrf?: bool|null, // Default: null
 *             csrf_token_id?: scalar|null, // Default: "logout"
 *             csrf_parameter?: scalar|null, // Default: "_csrf_token"
 *             csrf_token_manager?: scalar|null,
 *             path?: scalar|null, // Default: "/logout"
 *             target?: scalar|null, // Default: "/"
 *             invalidate_session?: bool, // Default: true
 *             clear_site_data?: list<"*"|"cache"|"cookies"|"storage"|"executionContexts">,
 *             delete_cookies?: array<string, array{ // Default: []
 *                 path?: scalar|null, // Default: null
 *                 domain?: scalar|null, // Default: null
 *                 secure?: scalar|null, // Default: false
 *                 samesite?: scalar|null, // Default: null
 *                 partitioned?: scalar|null, // Default: false
 *             }>,
 *         },
 *         switch_user?: array{
 *             provider?: scalar|null,
 *             parameter?: scalar|null, // Default: "_switch_user"
 *             role?: scalar|null, // Default: "ROLE_ALLOWED_TO_SWITCH"
 *             target_route?: scalar|null, // Default: null
 *         },
 *         required_badges?: list<scalar|null>,
 *         custom_authenticators?: list<scalar|null>,
 *         login_throttling?: array{
 *             limiter?: scalar|null, // A service id implementing "Symfony\Component\HttpFoundation\RateLimiter\RequestRateLimiterInterface".
 *             max_attempts?: int, // Default: 5
 *             interval?: scalar|null, // Default: "1 minute"
 *             lock_factory?: scalar|null, // The service ID of the lock factory used by the login rate limiter (or null to disable locking). // Default: null
 *             cache_pool?: string, // The cache pool to use for storing the limiter state // Default: "cache.rate_limiter"
 *             storage_service?: string, // The service ID of a custom storage implementation, this precedes any configured "cache_pool" // Default: null
 *         },
 *         oauth?: array{
 *             provider?: scalar|null,
 *             remember_me?: bool, // Default: true
 *             success_handler?: scalar|null,
 *             failure_handler?: scalar|null,
 *             check_path?: scalar|null, // Default: "/login_check"
 *             use_forward?: bool, // Default: false
 *             login_path: scalar|null,
 *             always_use_default_target_path?: bool, // Default: false
 *             default_target_path?: scalar|null, // Default: "/"
 *             target_path_parameter?: scalar|null, // Default: "_target_path"
 *             use_referer?: bool, // Default: false
 *             failure_path?: scalar|null, // Default: null
 *             failure_forward?: bool, // Default: false
 *             failure_path_parameter?: scalar|null, // Default: "_failure_path"
 *             oauth_user_provider: array{
 *                 orm?: array{
 *                     class: scalar|null,
 *                     manager_name?: scalar|null, // Default: null
 *                     properties: array<string, scalar|null>,
 *                 },
 *                 service?: scalar|null,
 *                 oauth?: scalar|null,
 *             },
 *             resource_owners: array<string, scalar|null>,
 *         },
 *         x509?: array{
 *             provider?: scalar|null,
 *             user?: scalar|null, // Default: "SSL_CLIENT_S_DN_Email"
 *             credentials?: scalar|null, // Default: "SSL_CLIENT_S_DN"
 *             user_identifier?: scalar|null, // Default: "emailAddress"
 *         },
 *         remote_user?: array{
 *             provider?: scalar|null,
 *             user?: scalar|null, // Default: "REMOTE_USER"
 *         },
 *         jwt?: array{
 *             provider?: scalar|null, // Default: null
 *             authenticator?: scalar|null, // Default: "lexik_jwt_authentication.security.jwt_authenticator"
 *         },
 *         login_link?: array{
 *             check_route: scalar|null, // Route that will validate the login link - e.g. "app_login_link_verify".
 *             check_post_only?: scalar|null, // If true, only HTTP POST requests to "check_route" will be handled by the authenticator. // Default: false
 *             signature_properties: list<scalar|null>,
 *             lifetime?: int, // The lifetime of the login link in seconds. // Default: 600
 *             max_uses?: int, // Max number of times a login link can be used - null means unlimited within lifetime. // Default: null
 *             used_link_cache?: scalar|null, // Cache service id used to expired links of max_uses is set.
 *             success_handler?: scalar|null, // A service id that implements Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface.
 *             failure_handler?: scalar|null, // A service id that implements Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface.
 *             provider?: scalar|null, // The user provider to load users from.
 *             secret?: scalar|null, // Default: "%kernel.secret%"
 *             always_use_default_target_path?: bool, // Default: false
 *             default_target_path?: scalar|null, // Default: "/"
 *             login_path?: scalar|null, // Default: "/login"
 *             target_path_parameter?: scalar|null, // Default: "_target_path"
 *             use_referer?: bool, // Default: false
 *             failure_path?: scalar|null, // Default: null
 *             failure_forward?: bool, // Default: false
 *             failure_path_parameter?: scalar|null, // Default: "_failure_path"
 *         },
 *         form_login?: array{
 *             provider?: scalar|null,
 *             remember_me?: bool, // Default: true
 *             success_handler?: scalar|null,
 *             failure_handler?: scalar|null,
 *             check_path?: scalar|null, // Default: "/login_check"
 *             use_forward?: bool, // Default: false
 *             login_path?: scalar|null, // Default: "/login"
 *             username_parameter?: scalar|null, // Default: "_username"
 *             password_parameter?: scalar|null, // Default: "_password"
 *             csrf_parameter?: scalar|null, // Default: "_csrf_token"
 *             csrf_token_id?: scalar|null, // Default: "authenticate"
 *             enable_csrf?: bool, // Default: false
 *             post_only?: bool, // Default: true
 *             form_only?: bool, // Default: false
 *             always_use_default_target_path?: bool, // Default: false
 *             default_target_path?: scalar|null, // Default: "/"
 *             target_path_parameter?: scalar|null, // Default: "_target_path"
 *             use_referer?: bool, // Default: false
 *             failure_path?: scalar|null, // Default: null
 *             failure_forward?: bool, // Default: false
 *             failure_path_parameter?: scalar|null, // Default: "_failure_path"
 *         },
 *         form_login_ldap?: array{
 *             provider?: scalar|null,
 *             remember_me?: bool, // Default: true
 *             success_handler?: scalar|null,
 *             failure_handler?: scalar|null,
 *             check_path?: scalar|null, // Default: "/login_check"
 *             use_forward?: bool, // Default: false
 *             login_path?: scalar|null, // Default: "/login"
 *             username_parameter?: scalar|null, // Default: "_username"
 *             password_parameter?: scalar|null, // Default: "_password"
 *             csrf_parameter?: scalar|null, // Default: "_csrf_token"
 *             csrf_token_id?: scalar|null, // Default: "authenticate"
 *             enable_csrf?: bool, // Default: false
 *             post_only?: bool, // Default: true
 *             form_only?: bool, // Default: false
 *             always_use_default_target_path?: bool, // Default: false
 *             default_target_path?: scalar|null, // Default: "/"
 *             target_path_parameter?: scalar|null, // Default: "_target_path"
 *             use_referer?: bool, // Default: false
 *             failure_path?: scalar|null, // Default: null
 *             failure_forward?: bool, // Default: false
 *             failure_path_parameter?: scalar|null, // Default: "_failure_path"
 *             service?: scalar|null, // Default: "ldap"
 *             dn_string?: scalar|null, // Default: "{user_identifier}"
 *             query_string?: scalar|null,
 *             search_dn?: scalar|null, // Default: ""
 *             search_password?: scalar|null, // Default: ""
 *         },
 *         json_login?: array{
 *             provider?: scalar|null,
 *             remember_me?: bool, // Default: true
 *             success_handler?: scalar|null,
 *             failure_handler?: scalar|null,
 *             check_path?: scalar|null, // Default: "/login_check"
 *             use_forward?: bool, // Default: false
 *             login_path?: scalar|null, // Default: "/login"
 *             username_path?: scalar|null, // Default: "username"
 *             password_path?: scalar|null, // Default: "password"
 *         },
 *         json_login_ldap?: array{
 *             provider?: scalar|null,
 *             remember_me?: bool, // Default: true
 *             success_handler?: scalar|null,
 *             failure_handler?: scalar|null,
 *             check_path?: scalar|null, // Default: "/login_check"
 *             use_forward?: bool, // Default: false
 *             login_path?: scalar|null, // Default: "/login"
 *             username_path?: scalar|null, // Default: "username"
 *             password_path?: scalar|null, // Default: "password"
 *             service?: scalar|null, // Default: "ldap"
 *             dn_string?: scalar|null, // Default: "{user_identifier}"
 *             query_string?: scalar|null,
 *             search_dn?: scalar|null, // Default: ""
 *             search_password?: scalar|null, // Default: ""
 *         },
 *         access_token?: array{
 *             provider?: scalar|null,
 *             remember_me?: bool, // Default: true
 *             success_handler?: scalar|null,
 *             failure_handler?: scalar|null,
 *             realm?: scalar|null, // Default: null
 *             token_extractors?: list<scalar|null>,
 *             token_handler: string|array{
 *                 id?: scalar|null,
 *                 oidc_user_info?: string|array{
 *                     base_uri: scalar|null, // Base URI of the userinfo endpoint on the OIDC server, or the OIDC server URI to use the discovery (require "discovery" to be configured).
 *                     discovery?: array{ // Enable the OIDC discovery.
 *                         cache?: array{
 *                             id: scalar|null, // Cache service id to use to cache the OIDC discovery configuration.
 *                         },
 *                     },
 *                     claim?: scalar|null, // Claim which contains the user identifier (e.g. sub, email, etc.). // Default: "sub"
 *                     client?: scalar|null, // HttpClient service id to use to call the OIDC server.
 *                 },
 *                 oidc?: array{
 *                     discovery?: array{ // Enable the OIDC discovery.
 *                         base_uri: list<scalar|null>,
 *                         cache?: array{
 *                             id: scalar|null, // Cache service id to use to cache the OIDC discovery configuration.
 *                         },
 *                     },
 *                     claim?: scalar|null, // Claim which contains the user identifier (e.g.: sub, email..). // Default: "sub"
 *                     audience: scalar|null, // Audience set in the token, for validation purpose.
 *                     issuers: list<scalar|null>,
 *                     algorithm?: array<mixed>,
 *                     algorithms: list<scalar|null>,
 *                     key?: scalar|null, // Deprecated: The "key" option is deprecated and will be removed in 8.0. Use the "keyset" option instead. // JSON-encoded JWK used to sign the token (must contain a "kty" key).
 *                     keyset?: scalar|null, // JSON-encoded JWKSet used to sign the token (must contain a list of valid public keys).
 *                     encryption?: bool|array{
 *                         enabled?: bool, // Default: false
 *                         enforce?: bool, // When enabled, the token shall be encrypted. // Default: false
 *                         algorithms: list<scalar|null>,
 *                         keyset: scalar|null, // JSON-encoded JWKSet used to decrypt the token (must contain a list of valid private keys).
 *                     },
 *                 },
 *                 cas?: array{
 *                     validation_url: scalar|null, // CAS server validation URL
 *                     prefix?: scalar|null, // CAS prefix // Default: "cas"
 *                     http_client?: scalar|null, // HTTP Client service // Default: null
 *                 },
 *                 oauth2?: scalar|null,
 *             },
 *         },
 *         http_basic?: array{
 *             provider?: scalar|null,
 *             realm?: scalar|null, // Default: "Secured Area"
 *         },
 *         http_basic_ldap?: array{
 *             provider?: scalar|null,
 *             realm?: scalar|null, // Default: "Secured Area"
 *             service?: scalar|null, // Default: "ldap"
 *             dn_string?: scalar|null, // Default: "{user_identifier}"
 *             query_string?: scalar|null,
 *             search_dn?: scalar|null, // Default: ""
 *             search_password?: scalar|null, // Default: ""
 *         },
 *         remember_me?: array{
 *             secret?: scalar|null, // Default: "%kernel.secret%"
 *             service?: scalar|null,
 *             user_providers?: list<scalar|null>,
 *             catch_exceptions?: bool, // Default: true
 *             signature_properties?: list<scalar|null>,
 *             token_provider?: string|array{
 *                 service?: scalar|null, // The service ID of a custom remember-me token provider.
 *                 doctrine?: bool|array{
 *                     enabled?: bool, // Default: false
 *                     connection?: scalar|null, // Default: null
 *                 },
 *             },
 *             token_verifier?: scalar|null, // The service ID of a custom rememberme token verifier.
 *             name?: scalar|null, // Default: "REMEMBERME"
 *             lifetime?: int, // Default: 31536000
 *             path?: scalar|null, // Default: "/"
 *             domain?: scalar|null, // Default: null
 *             secure?: true|false|"auto", // Default: null
 *             httponly?: bool, // Default: true
 *             samesite?: null|"lax"|"strict"|"none", // Default: "lax"
 *             always_remember_me?: bool, // Default: false
 *             remember_me_parameter?: scalar|null, // Default: "_remember_me"
 *         },
 *         refresh_jwt?: array{
 *             check_path?: scalar|null, // Default: null
 *             provider?: scalar|null,
 *             success_handler?: scalar|null,
 *             failure_handler?: scalar|null,
 *         },
 *     }>,
 *     access_control?: list<array{ // Default: []
 *         request_matcher?: scalar|null, // Default: null
 *         requires_channel?: scalar|null, // Default: null
 *         path?: scalar|null, // Use the urldecoded format. // Default: null
 *         host?: scalar|null, // Default: null
 *         port?: int, // Default: null
 *         ips?: list<scalar|null>,
 *         attributes?: array<string, scalar|null>,
 *         route?: scalar|null, // Default: null
 *         methods?: list<scalar|null>,
 *         allow_if?: scalar|null, // Default: null
 *         roles?: list<scalar|null>,
 *     }>,
 *     role_hierarchy?: array<string, string|list<scalar|null>>,
 * }
 * @psalm-type TwigConfig = array{
 *     form_themes?: list<scalar|null>,
 *     globals?: array<string, array{ // Default: []
 *         id?: scalar|null,
 *         type?: scalar|null,
 *         value?: mixed,
 *     }>,
 *     autoescape_service?: scalar|null, // Default: null
 *     autoescape_service_method?: scalar|null, // Default: null
 *     base_template_class?: scalar|null, // Deprecated: The child node "base_template_class" at path "twig.base_template_class" is deprecated.
 *     cache?: scalar|null, // Default: true
 *     charset?: scalar|null, // Default: "%kernel.charset%"
 *     debug?: bool, // Default: "%kernel.debug%"
 *     strict_variables?: bool, // Default: "%kernel.debug%"
 *     auto_reload?: scalar|null,
 *     optimizations?: int,
 *     default_path?: scalar|null, // The default path used to load templates. // Default: "%kernel.project_dir%/templates"
 *     file_name_pattern?: list<scalar|null>,
 *     paths?: array<string, mixed>,
 *     date?: array{ // The default format options used by the date filter.
 *         format?: scalar|null, // Default: "F j, Y H:i"
 *         interval_format?: scalar|null, // Default: "%d days"
 *         timezone?: scalar|null, // The timezone used when formatting dates, when set to null, the timezone returned by date_default_timezone_get() is used. // Default: null
 *     },
 *     number_format?: array{ // The default format options for the number_format filter.
 *         decimals?: int, // Default: 0
 *         decimal_point?: scalar|null, // Default: "."
 *         thousands_separator?: scalar|null, // Default: ","
 *     },
 *     mailer?: array{
 *         html_to_text_converter?: scalar|null, // A service implementing the "Symfony\Component\Mime\HtmlToTextConverter\HtmlToTextConverterInterface". // Default: null
 *     },
 * }
 * @psalm-type WebProfilerConfig = array{
 *     toolbar?: bool|array{ // Profiler toolbar configuration
 *         enabled?: bool, // Default: false
 *         ajax_replace?: bool, // Replace toolbar on AJAX requests // Default: false
 *     },
 *     intercept_redirects?: bool, // Default: false
 *     excluded_ajax_paths?: scalar|null, // Default: "^/((index|app(_[\\w]+)?)\\.php/)?_wdt"
 * }
 * @psalm-type TwigExtraConfig = array{
 *     cache?: bool|array{
 *         enabled?: bool, // Default: false
 *     },
 *     html?: bool|array{
 *         enabled?: bool, // Default: false
 *     },
 *     markdown?: bool|array{
 *         enabled?: bool, // Default: false
 *     },
 *     intl?: bool|array{
 *         enabled?: bool, // Default: false
 *     },
 *     cssinliner?: bool|array{
 *         enabled?: bool, // Default: false
 *     },
 *     inky?: bool|array{
 *         enabled?: bool, // Default: false
 *     },
 *     string?: bool|array{
 *         enabled?: bool, // Default: true
 *     },
 *     commonmark?: array{
 *         renderer?: array{ // Array of options for rendering HTML.
 *             block_separator?: scalar|null,
 *             inner_separator?: scalar|null,
 *             soft_break?: scalar|null,
 *         },
 *         html_input?: "strip"|"allow"|"escape", // How to handle HTML input.
 *         allow_unsafe_links?: bool, // Remove risky link and image URLs by setting this to false. // Default: true
 *         max_nesting_level?: int, // The maximum nesting level for blocks. // Default: 9223372036854775807
 *         max_delimiters_per_line?: int, // The maximum number of strong/emphasis delimiters per line. // Default: 9223372036854775807
 *         slug_normalizer?: array{ // Array of options for configuring how URL-safe slugs are created.
 *             instance?: mixed,
 *             max_length?: int, // Default: 255
 *             unique?: mixed,
 *         },
 *         commonmark?: array{ // Array of options for configuring the CommonMark core extension.
 *             enable_em?: bool, // Default: true
 *             enable_strong?: bool, // Default: true
 *             use_asterisk?: bool, // Default: true
 *             use_underscore?: bool, // Default: true
 *             unordered_list_markers?: list<scalar|null>,
 *         },
 *         ...<mixed>
 *     },
 * }
 * @psalm-type LexikJwtAuthenticationConfig = array{
 *     public_key?: scalar|null, // The key used to sign tokens (useless for HMAC). If not set, the key will be automatically computed from the secret key. // Default: null
 *     additional_public_keys?: list<scalar|null>,
 *     secret_key?: scalar|null, // The key used to sign tokens. It can be a raw secret (for HMAC), a raw RSA/ECDSA key or the path to a file itself being plaintext or PEM. // Default: null
 *     pass_phrase?: scalar|null, // The key passphrase (useless for HMAC) // Default: ""
 *     token_ttl?: scalar|null, // Default: 3600
 *     allow_no_expiration?: bool, // Allow tokens without "exp" claim (i.e. indefinitely valid, no lifetime) to be considered valid. Caution: usage of this should be rare. // Default: false
 *     clock_skew?: scalar|null, // Default: 0
 *     encoder?: array{
 *         service?: scalar|null, // Default: "lexik_jwt_authentication.encoder.lcobucci"
 *         signature_algorithm?: scalar|null, // Default: "RS256"
 *     },
 *     user_id_claim?: scalar|null, // Default: "username"
 *     token_extractors?: array{
 *         authorization_header?: bool|array{
 *             enabled?: bool, // Default: true
 *             prefix?: scalar|null, // Default: "Bearer"
 *             name?: scalar|null, // Default: "Authorization"
 *         },
 *         cookie?: bool|array{
 *             enabled?: bool, // Default: false
 *             name?: scalar|null, // Default: "BEARER"
 *         },
 *         query_parameter?: bool|array{
 *             enabled?: bool, // Default: false
 *             name?: scalar|null, // Default: "bearer"
 *         },
 *         split_cookie?: bool|array{
 *             enabled?: bool, // Default: false
 *             cookies?: list<scalar|null>,
 *         },
 *     },
 *     remove_token_from_body_when_cookies_used?: scalar|null, // Default: true
 *     set_cookies?: array<string, array{ // Default: []
 *         lifetime?: scalar|null, // The cookie lifetime. If null, the "token_ttl" option value will be used // Default: null
 *         samesite?: "none"|"lax"|"strict", // Default: "lax"
 *         path?: scalar|null, // Default: "/"
 *         domain?: scalar|null, // Default: null
 *         secure?: scalar|null, // Default: true
 *         httpOnly?: scalar|null, // Default: true
 *         partitioned?: scalar|null, // Default: false
 *         split?: list<scalar|null>,
 *     }>,
 *     api_platform?: bool|array{ // API Platform compatibility: add check_path in OpenAPI documentation.
 *         enabled?: bool, // Default: false
 *         check_path?: scalar|null, // The login check path to add in OpenAPI. // Default: null
 *         username_path?: scalar|null, // The path to the username in the JSON body. // Default: null
 *         password_path?: scalar|null, // The path to the password in the JSON body. // Default: null
 *     },
 *     access_token_issuance?: bool|array{
 *         enabled?: bool, // Default: false
 *         signature?: array{
 *             algorithm: scalar|null, // The algorithm use to sign the access tokens.
 *             key: scalar|null, // The signature key. It shall be JWK encoded.
 *         },
 *         encryption?: bool|array{
 *             enabled?: bool, // Default: false
 *             key_encryption_algorithm: scalar|null, // The key encryption algorithm is used to encrypt the token.
 *             content_encryption_algorithm: scalar|null, // The key encryption algorithm is used to encrypt the token.
 *             key: scalar|null, // The encryption key. It shall be JWK encoded.
 *         },
 *     },
 *     access_token_verification?: bool|array{
 *         enabled?: bool, // Default: false
 *         signature?: array{
 *             header_checkers?: list<scalar|null>,
 *             claim_checkers?: list<scalar|null>,
 *             mandatory_claims?: list<scalar|null>,
 *             allowed_algorithms?: list<scalar|null>,
 *             keyset: scalar|null, // The signature keyset. It shall be JWKSet encoded.
 *         },
 *         encryption?: bool|array{
 *             enabled?: bool, // Default: false
 *             continue_on_decryption_failure?: bool, // If enable, non-encrypted tokens or tokens that failed during decryption or verification processes are accepted. // Default: false
 *             header_checkers?: list<scalar|null>,
 *             allowed_key_encryption_algorithms?: list<scalar|null>,
 *             allowed_content_encryption_algorithms?: list<scalar|null>,
 *             keyset: scalar|null, // The encryption keyset. It shall be JWKSet encoded.
 *         },
 *     },
 *     blocklist_token?: bool|array{
 *         enabled?: bool, // Default: false
 *         cache?: scalar|null, // Storage to track blocked tokens // Default: "cache.app"
 *     },
 * }
 * @psalm-type HwiOauthConfig = array{
 *     firewall_names?: list<scalar|null>,
 *     target_path_parameter?: scalar|null, // Default: null
 *     target_path_domains_whitelist?: list<scalar|null>,
 *     use_referer?: bool, // Default: false
 *     failed_use_referer?: bool, // Default: false
 *     failed_auth_path?: scalar|null, // Default: "hwi_oauth_connect"
 *     grant_rule?: scalar|null, // Default: "IS_AUTHENTICATED_REMEMBERED"
 *     connect?: array{
 *         confirmation?: bool, // Default: true
 *         account_connector?: scalar|null,
 *         registration_form_handler?: scalar|null,
 *         registration_form?: scalar|null,
 *     },
 *     resource_owners: array<string, array{ // Default: []
 *         base_url?: scalar|null,
 *         access_token_url?: scalar|null,
 *         authorization_url?: scalar|null,
 *         request_token_url?: scalar|null,
 *         revoke_token_url?: scalar|null,
 *         infos_url?: scalar|null,
 *         client_id?: scalar|null,
 *         client_secret?: scalar|null,
 *         realm?: scalar|null,
 *         scope?: scalar|null,
 *         user_response_class?: scalar|null,
 *         service?: scalar|null,
 *         class?: scalar|null,
 *         type?: scalar|null,
 *         use_authorization_to_get_token?: scalar|null,
 *         paths?: array<string, mixed>,
 *         options?: array<string, scalar|null>,
 *         ...<mixed>
 *     }>,
 * }
 * @psalm-type FosElasticaConfig = array{
 *     clients?: array<string, array{ // Default: []
 *         connections?: list<array{ // Default: []
 *             url?: scalar|null,
 *             username?: scalar|null,
 *             password?: scalar|null,
 *             host?: scalar|null,
 *             port?: scalar|null,
 *             proxy?: scalar|null,
 *             auth_type?: scalar|null,
 *             http_error_codes?: list<scalar|null>,
 *             aws_access_key_id?: scalar|null,
 *             aws_secret_access_key?: scalar|null,
 *             aws_region?: scalar|null,
 *             aws_session_token?: scalar|null,
 *             aws_credential_provider?: scalar|null,
 *             ssl?: bool, // Default: false
 *             logger?: scalar|null, // Default: "fos_elastica.logger"
 *             compression?: bool, // Default: false
 *             headers?: array<string, scalar|null>,
 *             curl?: array<string, scalar|null>,
 *             transport?: scalar|null,
 *             timeout?: scalar|null,
 *             connectTimeout?: scalar|null,
 *             retryOnConflict?: scalar|null, // Default: 0
 *             persistent?: bool, // Default: true
 *         }>,
 *         timeout?: scalar|null,
 *         connectTimeout?: scalar|null,
 *         headers?: scalar|null,
 *         connectionStrategy?: scalar|null, // Default: "Simple"
 *     }>,
 *     indexes?: array<string, array{ // Default: []
 *         index_name?: scalar|null, // Defaults to the name of the index, but can be modified if the index name is different in ElasticSearch
 *         indexable_callback?: mixed,
 *         use_alias?: bool, // Default: false
 *         client?: scalar|null,
 *         finder?: scalar|null, // Default: false
 *         persistence?: array{
 *             driver?: scalar|null, // Default: "orm"
 *             model?: scalar|null, // Default: null
 *             repository?: scalar|null,
 *             identifier?: scalar|null, // Default: "id"
 *             provider?: array{
 *                 batch_size?: scalar|null, // Default: 100
 *                 clear_object_manager?: scalar|null, // Default: true
 *                 debug_logging?: bool, // Default: true
 *                 query_builder_method?: scalar|null, // Default: "createQueryBuilder"
 *                 locale?: scalar|null,
 *                 service?: scalar|null,
 *             },
 *             listener?: bool|array{
 *                 enabled?: bool, // Default: true
 *                 insert?: scalar|null, // Default: true
 *                 update?: scalar|null, // Default: true
 *                 delete?: scalar|null, // Default: true
 *                 flush?: scalar|null, // Default: true
 *                 defer?: bool, // Default: false
 *                 logger?: scalar|null, // Default: false
 *                 service?: scalar|null,
 *             },
 *             finder?: array{
 *                 service?: scalar|null,
 *             },
 *             elastica_to_model_transformer?: array{
 *                 hints?: list<array{ // Default: []
 *                     name?: scalar|null,
 *                     value?: scalar|null,
 *                 }>,
 *                 hydrate?: bool, // Default: true
 *                 ignore_missing?: bool, // Silently ignore results returned from Elasticsearch without corresponding persistent object. // Default: false
 *                 query_builder_method?: scalar|null, // Default: "createQueryBuilder"
 *                 service?: scalar|null,
 *             },
 *             model_to_elastica_transformer?: array{
 *                 service?: scalar|null,
 *             },
 *             persister?: array{
 *                 refresh?: "true"|"wait_for"|"false",
 *                 service?: scalar|null,
 *             },
 *         },
 *         serializer?: array{
 *             groups?: list<scalar|null>,
 *             version?: scalar|null,
 *             serialize_null?: bool, // Default: false
 *         },
 *         index_prototype?: array{
 *             analyzer?: scalar|null,
 *             persistence?: array{
 *                 driver?: scalar|null, // Default: "orm"
 *                 model?: scalar|null, // Default: null
 *                 repository?: scalar|null,
 *                 identifier?: scalar|null, // Default: "id"
 *                 provider?: array{
 *                     batch_size?: scalar|null, // Default: 100
 *                     clear_object_manager?: scalar|null, // Default: true
 *                     debug_logging?: bool, // Default: true
 *                     query_builder_method?: scalar|null, // Default: "createQueryBuilder"
 *                     locale?: scalar|null,
 *                     service?: scalar|null,
 *                 },
 *                 listener?: bool|array{
 *                     enabled?: bool, // Default: true
 *                     insert?: scalar|null, // Default: true
 *                     update?: scalar|null, // Default: true
 *                     delete?: scalar|null, // Default: true
 *                     flush?: scalar|null, // Default: true
 *                     defer?: bool, // Default: false
 *                     logger?: scalar|null, // Default: false
 *                     service?: scalar|null,
 *                 },
 *                 finder?: array{
 *                     service?: scalar|null,
 *                 },
 *                 elastica_to_model_transformer?: array{
 *                     hints?: list<array{ // Default: []
 *                         name?: scalar|null,
 *                         value?: scalar|null,
 *                     }>,
 *                     hydrate?: bool, // Default: true
 *                     ignore_missing?: bool, // Silently ignore results returned from Elasticsearch without corresponding persistent object. // Default: false
 *                     query_builder_method?: scalar|null, // Default: "createQueryBuilder"
 *                     service?: scalar|null,
 *                 },
 *                 model_to_elastica_transformer?: array{
 *                     service?: scalar|null,
 *                 },
 *                 persister?: array{
 *                     refresh?: "true"|"wait_for"|"false",
 *                     service?: scalar|null,
 *                 },
 *             },
 *             serializer?: array{
 *                 groups?: list<scalar|null>,
 *                 version?: scalar|null,
 *                 serialize_null?: bool, // Default: false
 *             },
 *         },
 *         settings?: mixed, // Default: []
 *         date_detection?: bool,
 *         dynamic_date_formats?: list<scalar|null>,
 *         analyzer?: scalar|null,
 *         numeric_detection?: bool,
 *         dynamic?: scalar|null,
 *         _id?: array{
 *             path?: scalar|null,
 *         },
 *         properties?: array<string, mixed>,
 *         dynamic_templates?: list<list<array{ // Default: []
 *                 match?: scalar|null,
 *                 unmatch?: scalar|null,
 *                 match_mapping_type?: scalar|null,
 *                 path_match?: scalar|null,
 *                 path_unmatch?: scalar|null,
 *                 match_pattern?: scalar|null,
 *                 mapping?: list<mixed>,
 *             }>>,
 *         _source?: array{
 *             excludes?: array<string, scalar|null>,
 *             includes?: array<string, scalar|null>,
 *             compress?: scalar|null,
 *             compress_threshold?: scalar|null,
 *             enabled?: scalar|null, // Default: true
 *         },
 *         _routing?: array{
 *             required?: scalar|null,
 *             path?: scalar|null,
 *         },
 *     }>,
 *     index_templates?: array<string, null|array{ // Default: []
 *         template_name?: scalar|null, // Defaults to the name of the index template, but can be modified if the index name is different in ElasticSearch
 *         template: scalar|null,
 *         client?: scalar|null,
 *         settings?: mixed, // Default: []
 *         date_detection?: bool,
 *         dynamic_date_formats?: list<scalar|null>,
 *         analyzer?: scalar|null,
 *         numeric_detection?: bool,
 *         dynamic?: scalar|null,
 *         _id?: array{
 *             path?: scalar|null,
 *         },
 *         properties?: array<string, mixed>,
 *         dynamic_templates?: list<list<array{ // Default: []
 *                 match?: scalar|null,
 *                 unmatch?: scalar|null,
 *                 match_mapping_type?: scalar|null,
 *                 path_match?: scalar|null,
 *                 path_unmatch?: scalar|null,
 *                 match_pattern?: scalar|null,
 *                 mapping?: list<mixed>,
 *             }>>,
 *         _source?: array{
 *             excludes?: array<string, scalar|null>,
 *             includes?: array<string, scalar|null>,
 *             compress?: scalar|null,
 *             compress_threshold?: scalar|null,
 *             enabled?: scalar|null, // Default: true
 *         },
 *         _routing?: array{
 *             required?: scalar|null,
 *             path?: scalar|null,
 *         },
 *     }>,
 *     default_client?: scalar|null, // Defaults to the first client defined
 *     default_index?: scalar|null, // Defaults to the first index defined
 *     default_manager?: scalar|null, // Default: "orm"
 *     messenger?: bool|array{
 *         enabled?: bool, // Default: false
 *         bus?: scalar|null, // Default: "messenger.default_bus"
 *     },
 *     serializer?: array{
 *         callback_class?: scalar|null, // Default: "FOS\\ElasticaBundle\\Serializer\\Callback"
 *         serializer?: scalar|null, // Default: "serializer"
 *     },
 * }
 * @psalm-type JmsSerializerConfig = array{
 *     twig_enabled?: scalar|null, // Default: "default"
 *     profiler?: scalar|null, // Default: true
 *     enum_support?: scalar|null, // Default: false
 *     default_value_property_reader_support?: scalar|null, // Default: false
 *     handlers?: array{
 *         datetime?: array{
 *             default_format?: scalar|null, // Default: "Y-m-d\\TH:i:sP"
 *             default_deserialization_formats?: list<scalar|null>,
 *             default_timezone?: scalar|null, // Default: "UTC"
 *             cdata?: scalar|null, // Default: true
 *         },
 *         array_collection?: array{
 *             initialize_excluded?: bool, // Default: false
 *         },
 *         symfony_uid?: array{
 *             default_format?: scalar|null, // Default: "canonical"
 *             cdata?: scalar|null, // Default: true
 *         },
 *     },
 *     subscribers?: array{
 *         doctrine_proxy?: array{
 *             initialize_excluded?: bool, // Default: false
 *             initialize_virtual_types?: bool, // Default: false
 *         },
 *     },
 *     object_constructors?: array{
 *         doctrine?: bool|array{
 *             enabled?: bool, // Default: true
 *             fallback_strategy?: "null"|"exception"|"fallback", // Default: "null"
 *         },
 *     },
 *     property_naming?: string|array{
 *         id?: scalar|null,
 *         separator?: scalar|null, // Default: "_"
 *         lower_case?: bool, // Default: true
 *     },
 *     expression_evaluator?: string|array{
 *         id?: scalar|null, // Default: "jms_serializer.expression_evaluator"
 *     },
 *     metadata?: array{
 *         warmup?: array{
 *             paths?: array{
 *                 included?: list<scalar|null>,
 *                 excluded?: list<scalar|null>,
 *             },
 *         },
 *         cache?: scalar|null, // Default: "file"
 *         debug?: bool, // Default: true
 *         file_cache?: array{
 *             dir?: scalar|null, // Default: null
 *         },
 *         include_interfaces?: bool, // Default: false
 *         auto_detection?: bool, // Default: true
 *         infer_types_from_doc_block?: bool, // Default: false
 *         infer_types_from_doctrine_metadata?: bool, // Infers type information from Doctrine metadata if no explicit type has been defined for a property. // Default: true
 *         directories?: array<string, array{ // Default: []
 *             path: scalar|null,
 *             namespace_prefix?: scalar|null, // Default: ""
 *         }>,
 *     },
 *     visitors?: array{
 *         json_serialization?: array{
 *             depth?: scalar|null,
 *             options?: scalar|null, // Default: 1024
 *         },
 *         json_deserialization?: array{
 *             options?: scalar|null, // Default: 0
 *             strict?: bool, // Default: false
 *         },
 *         xml_serialization?: array{
 *             version?: scalar|null,
 *             encoding?: scalar|null,
 *             format_output?: bool, // Default: false
 *             default_root_name?: scalar|null,
 *             default_root_ns?: scalar|null, // Default: ""
 *         },
 *         xml_deserialization?: array{
 *             doctype_whitelist?: list<scalar|null>,
 *             external_entities?: bool, // Default: false
 *             options?: scalar|null, // Default: 0
 *         },
 *     },
 *     default_context?: array{
 *         serialization?: string|array{
 *             id?: scalar|null,
 *             serialize_null?: scalar|null, // Flag if null values should be serialized
 *             enable_max_depth_checks?: scalar|null, // Flag to enable the max-depth exclusion strategy
 *             attributes?: array<string, scalar|null>,
 *             groups?: list<scalar|null>,
 *             version?: scalar|null, // Application version to use in exclusion strategies
 *         },
 *         deserialization?: string|array{
 *             id?: scalar|null,
 *             serialize_null?: scalar|null, // Flag if null values should be serialized
 *             enable_max_depth_checks?: scalar|null, // Flag to enable the max-depth exclusion strategy
 *             attributes?: array<string, scalar|null>,
 *             groups?: list<scalar|null>,
 *             version?: scalar|null, // Application version to use in exclusion strategies
 *         },
 *     },
 *     instances?: array<string, array{ // Default: []
 *         inherit?: bool, // Default: false
 *         enum_support?: scalar|null, // Default: false
 *         default_value_property_reader_support?: scalar|null, // Default: false
 *         handlers?: array{
 *             datetime?: array{
 *                 default_format?: scalar|null, // Default: "Y-m-d\\TH:i:sP"
 *                 default_deserialization_formats?: list<scalar|null>,
 *                 default_timezone?: scalar|null, // Default: "UTC"
 *                 cdata?: scalar|null, // Default: true
 *             },
 *             array_collection?: array{
 *                 initialize_excluded?: bool, // Default: false
 *             },
 *             symfony_uid?: array{
 *                 default_format?: scalar|null, // Default: "canonical"
 *                 cdata?: scalar|null, // Default: true
 *             },
 *         },
 *         subscribers?: array{
 *             doctrine_proxy?: array{
 *                 initialize_excluded?: bool, // Default: false
 *                 initialize_virtual_types?: bool, // Default: false
 *             },
 *         },
 *         object_constructors?: array{
 *             doctrine?: bool|array{
 *                 enabled?: bool, // Default: true
 *                 fallback_strategy?: "null"|"exception"|"fallback", // Default: "null"
 *             },
 *         },
 *         property_naming?: string|array{
 *             id?: scalar|null,
 *             separator?: scalar|null, // Default: "_"
 *             lower_case?: bool, // Default: true
 *         },
 *         expression_evaluator?: string|array{
 *             id?: scalar|null, // Default: "jms_serializer.expression_evaluator"
 *         },
 *         metadata?: array{
 *             warmup?: array{
 *                 paths?: array{
 *                     included?: list<scalar|null>,
 *                     excluded?: list<scalar|null>,
 *                 },
 *             },
 *             cache?: scalar|null, // Default: "file"
 *             debug?: bool, // Default: true
 *             file_cache?: array{
 *                 dir?: scalar|null, // Default: null
 *             },
 *             include_interfaces?: bool, // Default: false
 *             auto_detection?: bool, // Default: true
 *             infer_types_from_doc_block?: bool, // Default: false
 *             infer_types_from_doctrine_metadata?: bool, // Infers type information from Doctrine metadata if no explicit type has been defined for a property. // Default: true
 *             directories?: array<string, array{ // Default: []
 *                 path: scalar|null,
 *                 namespace_prefix?: scalar|null, // Default: ""
 *             }>,
 *         },
 *         visitors?: array{
 *             json_serialization?: array{
 *                 depth?: scalar|null,
 *                 options?: scalar|null, // Default: 1024
 *             },
 *             json_deserialization?: array{
 *                 options?: scalar|null, // Default: 0
 *                 strict?: bool, // Default: false
 *             },
 *             xml_serialization?: array{
 *                 version?: scalar|null,
 *                 encoding?: scalar|null,
 *                 format_output?: bool, // Default: false
 *                 default_root_name?: scalar|null,
 *                 default_root_ns?: scalar|null, // Default: ""
 *             },
 *             xml_deserialization?: array{
 *                 doctype_whitelist?: list<scalar|null>,
 *                 external_entities?: bool, // Default: false
 *                 options?: scalar|null, // Default: 0
 *             },
 *         },
 *         default_context?: array{
 *             serialization?: string|array{
 *                 id?: scalar|null,
 *                 serialize_null?: scalar|null, // Flag if null values should be serialized
 *                 enable_max_depth_checks?: scalar|null, // Flag to enable the max-depth exclusion strategy
 *                 attributes?: array<string, scalar|null>,
 *                 groups?: list<scalar|null>,
 *                 version?: scalar|null, // Application version to use in exclusion strategies
 *             },
 *             deserialization?: string|array{
 *                 id?: scalar|null,
 *                 serialize_null?: scalar|null, // Flag if null values should be serialized
 *                 enable_max_depth_checks?: scalar|null, // Flag to enable the max-depth exclusion strategy
 *                 attributes?: array<string, scalar|null>,
 *                 groups?: list<scalar|null>,
 *                 version?: scalar|null, // Application version to use in exclusion strategies
 *             },
 *         },
 *     }>,
 * }
 * @psalm-type WebpackEncoreConfig = array{
 *     output_path: scalar|null, // The path where Encore is building the assets - i.e. Encore.setOutputPath()
 *     crossorigin?: false|"anonymous"|"use-credentials", // crossorigin value when Encore.enableIntegrityHashes() is used, can be false (default), anonymous or use-credentials // Default: false
 *     preload?: bool, // preload all rendered script and link tags automatically via the http2 Link header. // Default: false
 *     cache?: bool, // Enable caching of the entry point file(s) // Default: false
 *     strict_mode?: bool, // Throw an exception if the entrypoints.json file is missing or an entry is missing from the data // Default: true
 *     builds?: array<string, scalar|null>,
 *     script_attributes?: array<string, scalar|null>,
 *     link_attributes?: array<string, scalar|null>,
 * }
 * @psalm-type GesdinetJwtRefreshTokenConfig = array{
 *     ttl?: int, // The default TTL for all authenticators. // Default: 2592000
 *     ttl_update?: bool, // The default update TTL flag for all authenticators. // Default: false
 *     firewall?: scalar|null, // Deprecated: The "firewall" node is deprecated without replacement. // Default: "api"
 *     user_provider?: scalar|null, // Deprecated: The "user_provider" node is deprecated without replacement. // Default: null
 *     user_identity_field?: scalar|null, // Deprecated: The "user_identity_field" node is deprecated without replacement. // Default: "username"
 *     manager_type?: scalar|null, // Set the type of object manager to use (default: orm) // Default: "orm"
 *     refresh_token_class?: scalar|null, // Set the refresh token class to use (default: Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken) // Default: null
 *     object_manager?: scalar|null, // Set the object manager to use (default: doctrine.orm.entity_manager) // Default: null
 *     user_checker?: scalar|null, // Deprecated: The "user_checker" node is deprecated without replacement. // Default: "security.user_checker"
 *     refresh_token_entity?: scalar|null, // Deprecated: The "refresh_token_entity" node is deprecated, use the "refresh_token_class" node instead. // Set the refresh token class to use (default: Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken) // Default: null
 *     entity_manager?: scalar|null, // Deprecated: The "entity_manager" node is deprecated, use the "object_manager" node instead. // Set the entity manager to use // Default: null
 *     single_use?: scalar|null, // When true, generate a new refresh token on consumption (deleting the old one) // Default: false
 *     token_parameter_name?: scalar|null, // The default request parameter name containing the refresh token for all authenticators. // Default: "refresh_token"
 *     doctrine_mappings?: bool, // Deprecated: The "doctrine_mappings" node is deprecated without replacement. // When true, resolving of Doctrine mapping is done automatically to use either ORM or ODM object manager // Default: true
 *     cookie?: bool|array{
 *         enabled?: bool, // Default: false
 *         same_site?: "none"|"lax"|"strict", // Default: "lax"
 *         path?: scalar|null, // Default: "/"
 *         domain?: scalar|null, // Default: null
 *         http_only?: scalar|null, // Default: true
 *         secure?: scalar|null, // Default: true
 *         partitioned?: scalar|null, // Default: false
 *         remove_token_from_body?: scalar|null, // Default: true
 *     },
 *     logout_firewall?: scalar|null, // Name of the firewall that triggers the logout event to hook into (default: api) // Default: "api"
 *     return_expiration?: scalar|null, // When true, the response will include the token expiration timestamp // Default: false
 *     return_expiration_parameter_name?: scalar|null, // The default response parameter name containing the refresh token expiration timestamp // Default: "refresh_token_expiration"
 * }
 * @psalm-type SymfonycastsResetPasswordConfig = array{
 *     request_password_repository: scalar|null, // A class that implements ResetPasswordRequestRepositoryInterface - usually your ResetPasswordRequestRepository.
 *     lifetime?: int, // The length of time in seconds that a password reset request is valid for after it is created. // Default: 3600
 *     throttle_limit?: int, // Another password reset cannot be made faster than this throttle time in seconds. // Default: 3600
 *     enable_garbage_collection?: bool, // Enable/Disable automatic garbage collection. // Default: true
 * }
 * @psalm-type SymfonycastsVerifyEmailConfig = array{
 *     lifetime?: int, // The length of time in seconds that a signed URI is valid for after it is created. // Default: 3600
 * }
 * @psalm-type SonataFormConfig = array{
 *     form_type?: scalar|null, // Must be one of standard, horizontal // Default: "standard"
 * }
 * @psalm-type SonataTwigConfig = array{
 *     form_type?: "standard"|"horizontal", // Style used in the forms, some of the widgets need to be wrapped in a special div element depending on this style. // Default: "standard"
 *     flashmessage?: array<string, array{ // Default: []
 *         css_class?: scalar|null,
 *         types?: list<scalar|null>,
 *     }>,
 * }
 * @psalm-type StimulusConfig = array{
 *     controller_paths?: list<scalar|null>,
 *     controllers_json?: scalar|null, // Default: "%kernel.project_dir%/assets/controllers.json"
 * }
 * @psalm-type BugsnagConfig = array{
 *     api_key?: scalar|null, // Default: null
 *     endpoint?: scalar|null, // Default: null
 *     callbacks?: bool, // Default: true
 *     user?: bool, // Default: true
 *     app_type?: scalar|null, // Default: null
 *     app_version?: scalar|null, // Default: null
 *     batch_sending?: bool, // Default: true
 *     hostname?: scalar|null, // Default: null
 *     send_code?: bool, // Default: true
 *     release_stage?: scalar|null, // Default: null
 *     strip_path?: scalar|null, // Default: null
 *     project_root?: scalar|null, // Default: null
 *     auto_notify?: bool, // Default: true
 *     resolver?: scalar|null, // Default: "Bugsnag\\BugsnagBundle\\Request\\SymfonyResolver"
 *     factory?: scalar|null, // Default: "Bugsnag\\BugsnagBundle\\DependencyInjection\\ClientFactory"
 *     client?: scalar|null, // Default: "Bugsnag\\Client"
 *     listener?: scalar|null, // Default: "Bugsnag\\BugsnagBundle\\EventListener\\BugsnagListener"
 *     notify_release_stages?: list<scalar|null>,
 *     filters?: list<scalar|null>,
 *     shutdown?: scalar|null, // Default: "Bugsnag\\BugsnagBundle\\EventListener\\BugsnagShutdown"
 *     strip_path_regex?: scalar|null, // Default: null
 *     project_root_regex?: scalar|null, // Default: null
 *     guzzle?: scalar|null, // Default: null
 *     memory_limit_increase?: scalar|null, // Default: false
 *     discard_classes?: list<scalar|null>,
 *     redacted_keys?: list<scalar|null>,
 *     feature_flags?: list<array{ // Default: []
 *         name: scalar|null,
 *         variant?: scalar|null,
 *     }>,
 *     max_breadcrumbs?: scalar|null, // Default: null
 * }
 * @psalm-type HttplugConfig = array{
 *     clients?: array<string, array{ // Default: []
 *         factory?: scalar|null, // The service id of a factory to use when creating the adapter. // Default: "httplug.factory.auto"
 *         service?: scalar|null, // The service id of the client to use. // Default: null
 *         public?: bool|null, // Set to true if you really cannot use dependency injection and need to make the client service public. // Default: null
 *         flexible_client?: bool, // Set to true to get the client wrapped in a FlexibleHttpClient which emulates async or sync behavior. // Default: false
 *         http_methods_client?: bool, // Set to true to get the client wrapped in a HttpMethodsClient which emulates provides functions for HTTP verbs. // Default: false
 *         batch_client?: bool, // Set to true to get the client wrapped in a BatchClient which allows you to send multiple request at the same time. // Default: false
 *         config?: mixed, // Default: []
 *         plugins?: list<array{ // Default: []
 *             authentication?: array<string, array{ // Default: []
 *                 type: "basic"|"bearer"|"wsse"|"service"|"query_param"|"header",
 *                 username?: scalar|null,
 *                 password?: scalar|null,
 *                 token?: scalar|null,
 *                 service?: scalar|null,
 *                 header_name?: scalar|null,
 *                 header_value?: scalar|null,
 *                 params?: list<scalar|null>,
 *             }>,
 *             cache?: bool|array{ // Configure HTTP caching, requires the php-http/cache-plugin package
 *                 enabled?: bool, // Default: false
 *                 cache_pool: scalar|null, // This must be a service id to a service implementing Psr\Cache\CacheItemPoolInterface
 *                 stream_factory?: scalar|null, // This must be a service id to a service implementing Psr\Http\Message\StreamFactoryInterface // Default: "httplug.psr17_stream_factory"
 *                 config?: array{
 *                     cache_key_generator?: scalar|null, // This must be a service id to a service implementing Http\Client\Common\Plugin\Cache\Generator\CacheKeyGenerator
 *                     cache_lifetime?: scalar|null, // The minimum time we should store a cache item
 *                     default_ttl?: scalar|null, // The default max age of a Response
 *                     blacklisted_paths?: list<scalar|null>,
 *                     hash_algo?: "md2"|"md4"|"md5"|"sha1"|"sha224"|"sha256"|"sha384"|"sha512/224"|"sha512/256"|"sha512"|"sha3-224"|"sha3-256"|"sha3-384"|"sha3-512"|"ripemd128"|"ripemd160"|"ripemd256"|"ripemd320"|"whirlpool"|"tiger128,3"|"tiger160,3"|"tiger192,3"|"tiger128,4"|"tiger160,4"|"tiger192,4"|"snefru"|"snefru256"|"gost"|"gost-crypto"|"adler32"|"crc32"|"crc32b"|"crc32c"|"fnv132"|"fnv1a32"|"fnv164"|"fnv1a64"|"joaat"|"murmur3a"|"murmur3c"|"murmur3f"|"xxh32"|"xxh64"|"xxh3"|"xxh128"|"haval128,3"|"haval160,3"|"haval192,3"|"haval224,3"|"haval256,3"|"haval128,4"|"haval160,4"|"haval192,4"|"haval224,4"|"haval256,4"|"haval128,5"|"haval160,5"|"haval192,5"|"haval224,5"|"haval256,5", // Hashing algorithm to use
 *                     methods?: list<scalar|null>,
 *                     cache_listeners?: list<scalar|null>,
 *                     respect_response_cache_directives?: mixed, // A list of cache directives to respect when caching responses. Omit or set to null to respect the default set of directives.
 *                 },
 *             },
 *             cookie?: bool|array{
 *                 enabled?: bool, // Default: false
 *                 cookie_jar: scalar|null, // This must be a service id to a service implementing Http\Message\CookieJar
 *             },
 *             history?: bool|array{
 *                 enabled?: bool, // Default: false
 *                 journal: scalar|null, // This must be a service id to a service implementing Http\Client\Common\Plugin\Journal
 *             },
 *             decoder?: bool|array{
 *                 enabled?: bool, // Default: false
 *                 use_content_encoding?: scalar|null, // Default: true
 *             },
 *             logger?: bool|array{
 *                 enabled?: bool, // Default: false
 *                 logger?: scalar|null, // This must be a service id to a service implementing Psr\Log\LoggerInterface // Default: "logger"
 *                 formatter?: scalar|null, // This must be a service id to a service implementing Http\Message\Formatter // Default: null
 *             },
 *             redirect?: bool|array{
 *                 enabled?: bool, // Default: false
 *                 preserve_header?: scalar|null, // Default: true
 *                 use_default_for_multiple?: scalar|null, // Default: true
 *             },
 *             retry?: bool|array{
 *                 enabled?: bool, // Default: false
 *                 retry?: scalar|null, // Default: 1
 *             },
 *             stopwatch?: bool|array{
 *                 enabled?: bool, // Default: false
 *                 stopwatch?: scalar|null, // This must be a service id to a service extending Symfony\Component\Stopwatch\Stopwatch // Default: "debug.stopwatch"
 *             },
 *             error?: bool|array{
 *                 enabled?: bool, // Default: false
 *                 only_server_exception?: scalar|null, // Default: false
 *             },
 *             throttle?: bool|array{
 *                 enabled?: bool, // Default: false
 *                 name: scalar|null, // Rate limiter service name from symfony/rate-limiter configuration. E.g. for a rate limiter http_client you specify limiter.http_client here
 *                 key?: scalar|null, // Key to avoid sharing this rate limiter with other clients or other services. You can use the name of the client for example. // Default: null
 *                 tokens?: int, // How many tokens spending per request // Default: 1
 *                 max_time?: float, // Maximum accepted waiting time in seconds // Default: null
 *             },
 *             reference?: bool|array{ // Reference to a plugin service
 *                 enabled?: bool, // Default: false
 *                 id: scalar|null, // Service id of a plugin
 *             },
 *             configurator?: bool|array{ // Configure a plugin with a configurator
 *                 enabled?: bool, // Default: false
 *                 id: scalar|null, // Service id of a plugin configurator
 *                 config?: list<mixed>,
 *             },
 *             add_host?: bool|array{ // Set scheme, host and port in the request URI.
 *                 enabled?: bool, // Default: false
 *                 host: scalar|null, // Host name including protocol and optionally the port number, e.g. https://api.local:8000
 *                 replace?: scalar|null, // Whether to replace the host if request already specifies one // Default: false
 *             },
 *             add_path?: bool|array{ // Add a base path to the request.
 *                 enabled?: bool, // Default: false
 *                 path: scalar|null, // Path to be added, e.g. /api/v1
 *             },
 *             base_uri?: bool|array{ // Set a base URI to the request.
 *                 enabled?: bool, // Default: false
 *                 uri: scalar|null, // Base Uri including protocol, optionally the port number and prepend path, e.g. https://api.local:8000/api
 *                 replace?: scalar|null, // Whether to replace the host if request already specifies one // Default: false
 *             },
 *             content_type?: bool|array{ // Detect the content type of a request body and set the Content-Type header if it is not already set.
 *                 enabled?: bool, // Default: false
 *                 skip_detection?: bool, // Whether to skip detection when request body is larger than size_limit // Default: false
 *                 size_limit?: scalar|null, // Skip content type detection if request body is larger than size_limit bytes
 *             },
 *             header_append?: bool|array{ // Append headers to the request. If the header already exists the value will be appended to the current value.
 *                 enabled?: bool, // Default: false
 *                 headers?: array<string, scalar|null>,
 *             },
 *             header_defaults?: bool|array{ // Set header to default value if it does not exist.
 *                 enabled?: bool, // Default: false
 *                 headers?: array<string, scalar|null>,
 *             },
 *             header_set?: bool|array{ // Set headers to requests. If the header does not exist it wil be set, if the header already exists it will be replaced.
 *                 enabled?: bool, // Default: false
 *                 headers?: array<string, scalar|null>,
 *             },
 *             header_remove?: bool|array{ // Remove headers from requests.
 *                 enabled?: bool, // Default: false
 *                 headers?: list<scalar|null>,
 *             },
 *             query_defaults?: bool|array{ // Sets query parameters to default value if they are not present in the request.
 *                 enabled?: bool, // Default: false
 *                 parameters?: array<string, scalar|null>,
 *             },
 *             request_seekable_body?: bool|array{ // Ensure that the request body is seekable so that several plugins can look into it.
 *                 enabled?: bool, // Default: false
 *                 use_file_buffer?: bool, // Whether to use a file buffer if the stream is too big for a memory buffer // Default: true
 *                 memory_buffer_size?: scalar|null, // Maximum memory size in bytes before using a file buffer if use_file_buffer is true. Defaults to 2097152 (2 MB)
 *             },
 *             response_seekable_body?: bool|array{ // Ensure that the response body is seekable so that several plugins can look into it.
 *                 enabled?: bool, // Default: false
 *                 use_file_buffer?: bool, // Whether to use a file buffer if the stream is too big for a memory buffer // Default: true
 *                 memory_buffer_size?: scalar|null, // Maximum memory size in bytes before using a file buffer if use_file_buffer is true. Defaults to 2097152 (2 MB)
 *             },
 *             vcr?: bool|array{ // Record response to be replayed during tests or development cycle.
 *                 enabled?: bool, // Default: false
 *                 mode: "record"|"replay"|"replay_or_record", // What should be the behavior of the plugin?
 *                 recorder?: scalar|null, // Which recorder to use. Can be "in_memory", "filesystem" or the ID of your service implementing Http\Client\Plugin\Vcr\Recorder\RecorderInterface and Http\Client\Plugin\Vcr\Recorder\PlayerInterface. When using filesystem, specify "fixtures_directory" as well. // Default: "filesystem"
 *                 naming_strategy?: scalar|null, // Which naming strategy to use. Add the ID of your service implementing Http\Client\Plugin\Vcr\NamingStrategy\NamingStrategyInterface to override the default one. // Default: "default"
 *                 naming_strategy_options?: array{ // See http://docs.php-http.org/en/latest/plugins/vcr.html#the-naming-strategy for more details
 *                     hash_headers?: list<scalar|null>,
 *                     hash_body_methods?: list<scalar|null>,
 *                 },
 *                 fixtures_directory?: scalar|null, // Where the responses will be stored and replay from when using the filesystem recorder. Should be accessible to your VCS.
 *             },
 *         }>,
 *     }>,
 *     plugins?: array{ // Global plugin configuration. Plugins need to be explicitly added to clients.
 *         authentication?: array<string, array{ // Default: []
 *             type: "basic"|"bearer"|"wsse"|"service"|"query_param"|"header",
 *             username?: scalar|null,
 *             password?: scalar|null,
 *             token?: scalar|null,
 *             service?: scalar|null,
 *             header_name?: scalar|null,
 *             header_value?: scalar|null,
 *             params?: list<scalar|null>,
 *         }>,
 *         cache?: bool|array{ // Configure HTTP caching, requires the php-http/cache-plugin package
 *             enabled?: bool, // Default: false
 *             cache_pool: scalar|null, // This must be a service id to a service implementing Psr\Cache\CacheItemPoolInterface
 *             stream_factory?: scalar|null, // This must be a service id to a service implementing Psr\Http\Message\StreamFactoryInterface // Default: "httplug.psr17_stream_factory"
 *             config?: array{
 *                 cache_key_generator?: scalar|null, // This must be a service id to a service implementing Http\Client\Common\Plugin\Cache\Generator\CacheKeyGenerator
 *                 cache_lifetime?: scalar|null, // The minimum time we should store a cache item
 *                 default_ttl?: scalar|null, // The default max age of a Response
 *                 blacklisted_paths?: list<scalar|null>,
 *                 hash_algo?: "md2"|"md4"|"md5"|"sha1"|"sha224"|"sha256"|"sha384"|"sha512/224"|"sha512/256"|"sha512"|"sha3-224"|"sha3-256"|"sha3-384"|"sha3-512"|"ripemd128"|"ripemd160"|"ripemd256"|"ripemd320"|"whirlpool"|"tiger128,3"|"tiger160,3"|"tiger192,3"|"tiger128,4"|"tiger160,4"|"tiger192,4"|"snefru"|"snefru256"|"gost"|"gost-crypto"|"adler32"|"crc32"|"crc32b"|"crc32c"|"fnv132"|"fnv1a32"|"fnv164"|"fnv1a64"|"joaat"|"murmur3a"|"murmur3c"|"murmur3f"|"xxh32"|"xxh64"|"xxh3"|"xxh128"|"haval128,3"|"haval160,3"|"haval192,3"|"haval224,3"|"haval256,3"|"haval128,4"|"haval160,4"|"haval192,4"|"haval224,4"|"haval256,4"|"haval128,5"|"haval160,5"|"haval192,5"|"haval224,5"|"haval256,5", // Hashing algorithm to use
 *                 methods?: list<scalar|null>,
 *                 cache_listeners?: list<scalar|null>,
 *                 respect_response_cache_directives?: mixed, // A list of cache directives to respect when caching responses. Omit or set to null to respect the default set of directives.
 *             },
 *         },
 *         cookie?: bool|array{
 *             enabled?: bool, // Default: false
 *             cookie_jar: scalar|null, // This must be a service id to a service implementing Http\Message\CookieJar
 *         },
 *         history?: bool|array{
 *             enabled?: bool, // Default: false
 *             journal: scalar|null, // This must be a service id to a service implementing Http\Client\Common\Plugin\Journal
 *         },
 *         decoder?: bool|array{
 *             enabled?: bool, // Default: true
 *             use_content_encoding?: scalar|null, // Default: true
 *         },
 *         logger?: bool|array{
 *             enabled?: bool, // Default: true
 *             logger?: scalar|null, // This must be a service id to a service implementing Psr\Log\LoggerInterface // Default: "logger"
 *             formatter?: scalar|null, // This must be a service id to a service implementing Http\Message\Formatter // Default: null
 *         },
 *         redirect?: bool|array{
 *             enabled?: bool, // Default: true
 *             preserve_header?: scalar|null, // Default: true
 *             use_default_for_multiple?: scalar|null, // Default: true
 *         },
 *         retry?: bool|array{
 *             enabled?: bool, // Default: true
 *             retry?: scalar|null, // Default: 1
 *         },
 *         stopwatch?: bool|array{
 *             enabled?: bool, // Default: true
 *             stopwatch?: scalar|null, // This must be a service id to a service extending Symfony\Component\Stopwatch\Stopwatch // Default: "debug.stopwatch"
 *         },
 *         error?: bool|array{
 *             enabled?: bool, // Default: false
 *             only_server_exception?: scalar|null, // Default: false
 *         },
 *         throttle?: bool|array{
 *             enabled?: bool, // Default: false
 *             name: scalar|null, // Rate limiter service name from symfony/rate-limiter configuration. E.g. for a rate limiter http_client you specify limiter.http_client here
 *             key?: scalar|null, // Key to avoid sharing this rate limiter with other clients or other services. You can use the name of the client for example. // Default: null
 *             tokens?: int, // How many tokens spending per request // Default: 1
 *             max_time?: float, // Maximum accepted waiting time in seconds // Default: null
 *         },
 *     },
 *     default_client_autowiring?: bool, // Set to false to not autowire ClientInterface and HttpAsyncClient. // Default: true
 *     main_alias?: array{ // Configure which service the main alias point to.
 *         client?: scalar|null, // Default: "httplug.client.default"
 *         psr18_client?: scalar|null, // Default: "httplug.psr18_client.default"
 *         psr17_request_factory?: scalar|null, // Default: "httplug.psr17_request_factory.default"
 *         psr17_response_factory?: scalar|null, // Default: "httplug.psr17_response_factory.default"
 *         psr17_stream_factory?: scalar|null, // Default: "httplug.psr17_stream_factory.default"
 *         psr17_uri_factory?: scalar|null, // Default: "httplug.psr17_uri_factory.default"
 *         psr17_uploaded_file_factory?: scalar|null, // Default: "httplug.psr17_uploaded_file_factory.default"
 *         psr17_server_request_factory?: scalar|null, // Default: "httplug.psr17_server_request_factory.default"
 *     },
 *     classes?: array{ // Overwrite a service class instead of using the discovery mechanism.
 *         client?: scalar|null, // Default: null
 *         psr18_client?: scalar|null, // Default: null
 *         psr17_request_factory?: scalar|null, // Default: null
 *         psr17_response_factory?: scalar|null, // Default: null
 *         psr17_stream_factory?: scalar|null, // Default: null
 *         psr17_uri_factory?: scalar|null, // Default: null
 *         psr17_uploaded_file_factory?: scalar|null, // Default: null
 *         psr17_server_request_factory?: scalar|null, // Default: null
 *     },
 *     profiling?: array{ // Extend the debug profiler with information about requests.
 *         enabled?: bool, // Turn the toolbar on or off. Defaults to kernel debug mode. // Default: true
 *         formatter?: scalar|null, // Default: null
 *         captured_body_length?: scalar|null, // Limit long HTTP message bodies to x characters. If set to 0 we do not read the message body. If null the body will not be truncated. Only available with the default formatter (FullHttpMessageFormatter). // Default: 0
 *     },
 *     discovery?: array{ // Control what clients should be found by the discovery.
 *         client?: scalar|null, // Set to "auto" to see auto discovered client in the web profiler. If provided a service id for a client then this client will be found by auto discovery. // Default: "auto"
 *         async_client?: scalar|null, // Set to "auto" to see auto discovered client in the web profiler. If provided a service id for a client then this client will be found by auto discovery. // Default: null
 *     },
 * }
 * @psalm-type ConfigType = array{
 *     imports?: ImportsConfig,
 *     parameters?: ParametersConfig,
 *     services?: ServicesConfig,
 *     framework?: FrameworkConfig,
 *     doctrine?: DoctrineConfig,
 *     doctrine_migrations?: DoctrineMigrationsConfig,
 *     eight_points_guzzle?: EightPointsGuzzleConfig,
 *     knp_menu?: KnpMenuConfig,
 *     sonata_block?: SonataBlockConfig,
 *     sonata_admin?: SonataAdminConfig,
 *     sonata_doctrine_orm_admin?: SonataDoctrineOrmAdminConfig,
 *     sonata_exporter?: SonataExporterConfig,
 *     monolog?: MonologConfig,
 *     maker?: MakerConfig,
 *     security?: SecurityConfig,
 *     twig?: TwigConfig,
 *     web_profiler?: WebProfilerConfig,
 *     twig_extra?: TwigExtraConfig,
 *     lexik_jwt_authentication?: LexikJwtAuthenticationConfig,
 *     hwi_oauth?: HwiOauthConfig,
 *     fos_elastica?: FosElasticaConfig,
 *     jms_serializer?: JmsSerializerConfig,
 *     webpack_encore?: WebpackEncoreConfig,
 *     gesdinet_jwt_refresh_token?: GesdinetJwtRefreshTokenConfig,
 *     symfonycasts_reset_password?: SymfonycastsResetPasswordConfig,
 *     symfonycasts_verify_email?: SymfonycastsVerifyEmailConfig,
 *     sonata_form?: SonataFormConfig,
 *     sonata_twig?: SonataTwigConfig,
 *     stimulus?: StimulusConfig,
 *     bugsnag?: BugsnagConfig,
 *     httplug?: HttplugConfig,
 *     "when@dev"?: array{
 *         imports?: ImportsConfig,
 *         parameters?: ParametersConfig,
 *         services?: ServicesConfig,
 *         framework?: FrameworkConfig,
 *         doctrine?: DoctrineConfig,
 *         doctrine_migrations?: DoctrineMigrationsConfig,
 *         eight_points_guzzle?: EightPointsGuzzleConfig,
 *         knp_menu?: KnpMenuConfig,
 *         sonata_block?: SonataBlockConfig,
 *         sonata_admin?: SonataAdminConfig,
 *         sonata_doctrine_orm_admin?: SonataDoctrineOrmAdminConfig,
 *         sonata_exporter?: SonataExporterConfig,
 *         monolog?: MonologConfig,
 *         maker?: MakerConfig,
 *         security?: SecurityConfig,
 *         twig?: TwigConfig,
 *         web_profiler?: WebProfilerConfig,
 *         twig_extra?: TwigExtraConfig,
 *         lexik_jwt_authentication?: LexikJwtAuthenticationConfig,
 *         hwi_oauth?: HwiOauthConfig,
 *         fos_elastica?: FosElasticaConfig,
 *         jms_serializer?: JmsSerializerConfig,
 *         webpack_encore?: WebpackEncoreConfig,
 *         gesdinet_jwt_refresh_token?: GesdinetJwtRefreshTokenConfig,
 *         symfonycasts_reset_password?: SymfonycastsResetPasswordConfig,
 *         symfonycasts_verify_email?: SymfonycastsVerifyEmailConfig,
 *         sonata_form?: SonataFormConfig,
 *         sonata_twig?: SonataTwigConfig,
 *         stimulus?: StimulusConfig,
 *         bugsnag?: BugsnagConfig,
 *         httplug?: HttplugConfig,
 *     },
 *     "when@test"?: array{
 *         imports?: ImportsConfig,
 *         parameters?: ParametersConfig,
 *         services?: ServicesConfig,
 *         framework?: FrameworkConfig,
 *         doctrine?: DoctrineConfig,
 *         doctrine_migrations?: DoctrineMigrationsConfig,
 *         eight_points_guzzle?: EightPointsGuzzleConfig,
 *         knp_menu?: KnpMenuConfig,
 *         sonata_block?: SonataBlockConfig,
 *         sonata_admin?: SonataAdminConfig,
 *         sonata_doctrine_orm_admin?: SonataDoctrineOrmAdminConfig,
 *         sonata_exporter?: SonataExporterConfig,
 *         monolog?: MonologConfig,
 *         maker?: MakerConfig,
 *         security?: SecurityConfig,
 *         twig?: TwigConfig,
 *         web_profiler?: WebProfilerConfig,
 *         twig_extra?: TwigExtraConfig,
 *         lexik_jwt_authentication?: LexikJwtAuthenticationConfig,
 *         hwi_oauth?: HwiOauthConfig,
 *         fos_elastica?: FosElasticaConfig,
 *         jms_serializer?: JmsSerializerConfig,
 *         webpack_encore?: WebpackEncoreConfig,
 *         gesdinet_jwt_refresh_token?: GesdinetJwtRefreshTokenConfig,
 *         symfonycasts_reset_password?: SymfonycastsResetPasswordConfig,
 *         symfonycasts_verify_email?: SymfonycastsVerifyEmailConfig,
 *         sonata_form?: SonataFormConfig,
 *         sonata_twig?: SonataTwigConfig,
 *         stimulus?: StimulusConfig,
 *         bugsnag?: BugsnagConfig,
 *         httplug?: HttplugConfig,
 *     },
 *     ...<string, ExtensionType|array{ // extra keys must follow the when@%env% pattern or match an extension alias
 *         imports?: ImportsConfig,
 *         parameters?: ParametersConfig,
 *         services?: ServicesConfig,
 *         ...<string, ExtensionType>,
 *     }>
 * }
 */
final class App
{
  /**
   * @param ConfigType $config
   *
   * @psalm-return ConfigType
   */
  public static function config(array $config): array
  {
    return AppReference::config($config);
  }
}

namespace Symfony\Component\Routing\Loader\Configurator;

/**
 * This class provides array-shapes for configuring the routes of an application.
 *
 * Example:
 *
 *     ```php
 *     // config/routes.php
 *     namespace Symfony\Component\Routing\Loader\Configurator;
 *
 *     return Routes::config([
 *         'controllers' => [
 *             'resource' => 'routing.controllers',
 *         ],
 *     ]);
 *     ```
 *
 * @psalm-type RouteConfig = array{
 *     path: string|array<string,string>,
 *     controller?: string,
 *     methods?: string|list<string>,
 *     requirements?: array<string,string>,
 *     defaults?: array<string,mixed>,
 *     options?: array<string,mixed>,
 *     host?: string|array<string,string>,
 *     schemes?: string|list<string>,
 *     condition?: string,
 *     locale?: string,
 *     format?: string,
 *     utf8?: bool,
 *     stateless?: bool,
 * }
 * @psalm-type ImportConfig = array{
 *     resource: string,
 *     type?: string,
 *     exclude?: string|list<string>,
 *     prefix?: string|array<string,string>,
 *     name_prefix?: string,
 *     trailing_slash_on_root?: bool,
 *     controller?: string,
 *     methods?: string|list<string>,
 *     requirements?: array<string,string>,
 *     defaults?: array<string,mixed>,
 *     options?: array<string,mixed>,
 *     host?: string|array<string,string>,
 *     schemes?: string|list<string>,
 *     condition?: string,
 *     locale?: string,
 *     format?: string,
 *     utf8?: bool,
 *     stateless?: bool,
 * }
 * @psalm-type AliasConfig = array{
 *     alias: string,
 *     deprecated?: array{package:string, version:string, message?:string},
 * }
 * @psalm-type RoutesConfig = array{
 *     "when@dev"?: array<string, RouteConfig|ImportConfig|AliasConfig>,
 *     "when@test"?: array<string, RouteConfig|ImportConfig|AliasConfig>,
 *     ...<string, RouteConfig|ImportConfig|AliasConfig>
 * }
 */
final class Routes
{
  /**
   * @param RoutesConfig $config
   *
   * @psalm-return RoutesConfig
   */
  public static function config(array $config): array
  {
    return $config;
  }
}
