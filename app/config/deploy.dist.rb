set :ssh_options, {
    config: false,
    forward_agent: true
}

set :stages,        %w()                  ### Insert your different stages and default here
set :default_stage, ""                    ### Run cap multistage:prepare to init config files
set :stage_dir,     "app/config/deploy"
require 'capistrano/ext/multistage'

set :app_path,    "app"
set :use_sudo,    false

# Deploy settings
set :use_composer, true
set :symfony_env_prod, "prod"
set :model_manager, "doctrine"

# files which are not in the git repo. When you add one, upload it yourself via ftp first
set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,   [app_path + "/logs", web_path + "/resources", "vendor", "backups", "Resources/Snapshots"]


# Which repo to use and how
set :repository,  "."                  # It's the path to your local repo
set :scm,         :git
set :deploy_via, :rsync_with_remote_cache
set :keep_releases,  3

# permissions
set :writable_dirs,       ["app/cache", "app/logs", "backups", "web/resources/featured", "web/resources/extract", "web/resources/programs", "web/resources/screenshots", "web/resources/thumbnails", "web/resources/apk", "web/resources/mediapackage", "Resources/Snapshots"]

set :webserver_user,      "www-data"
set :permission_method,   :acl
set :use_set_permissions, true

