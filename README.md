V3D GTT
========================

This application is delivered with Symfony2

1) Installing the Application
----------------------------------

### *Check php requirements*

Your php version needs to be >= 5.4 and database should be mysql.

### *Removing cache*

    sudo rm –rf gtt.v3d.fr/www/app/cache/*

### *The application is located in gtt.v3d.fr/www*

Copy all files from git to `gtt.v3d.fr/www`.

### *Check rights on important folders*

    sudo chmod -R 775 www
    sudo chown -R apache www/app/cache
    sudo chown -R apache www/app/logs

### *Set up database into parameters.yml*

    database_driver: pdo_mysql
    database_host: localhost // To modify
    database_port: null // To modify
    database_name: v3d_gtt // ! important
    database_user: root // To modify
    database_password: ae355670de // To modify
    mailer_transport: smtp
    mailer_host: 127.0.0.1
    mailer_user: null
    mailer_password: null
    locale: en
    secret: ThisTokenIsNotSoSecretChangeIt // To modify
    debug_toolbar: true
    debug_redirects: false use_assetic_controller: true

### *Check symfony2 requirements*

Access to `domain.com/app/check.php`

If any warning/error happens, treat it.

### *clear and set cache for prod*

    sudo rm –rf www/app/cache/*
    php www/app/console cache:clear –e prod

### *Application location*

`domain.com/web/app.php`

2) Updating application
-------------------------------------

Before starting using the application make sure everything is up to date.
To be sure go to `domain.com/web/app.php/update` and press the update button.

You are good to go