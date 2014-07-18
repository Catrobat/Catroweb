#Server settings
set :application, "Pocketcode"
set :domain,      ""                        #i.e. test.google.com
set :deploy_to,   ""                        #i.e. /var/www/google
set :app_path,    "app"
set :user, ""                               #ftp/ssh-user
set :password, ""                           #ssh-pwd
set :ssh_options, {:forward_agent => true}
ssh_options[:forward_agent] = true
ssh_options[:password] = ""

set :use_sudo, false

#Deploy settings
set :use_composer, true
# set :update_vendors, true
set :symfony_env_prod, "prod"
set :model_manager, "doctrine"
set :dump_assetic_assets, true


#files which are not in the git repo. When you add one, upload it yourself via ftp first
set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,     [app_path + "/logs", web_path + "/uploads", "vendor"] #uploads could be the projects folder

#Which repo to use and how
set :repository,  "/home/catroweb/projects/catroweb"        #It's the path to your local repo
set :scm,         :git
set :deploy_via, :rsync_with_remote_cache
set :keep_releases,  3

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain, :primary => true       # This may be the same as your `Web` server


#shitty permissions...
set :writable_dirs,       ["app/cache", "app/logs"]
set :webserver_user,      "www-data"
set :permission_method,   :acl
set :use_set_permissions, true

# for logging
# logger.level = Logger::MAX_LEVEL