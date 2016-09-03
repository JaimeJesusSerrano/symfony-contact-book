# symfony-contact-book
# ====================
A Symfony project created on March 4, 2016
In this document we show how install and running the application.
Executed in Ubuntu 15.10 64 bits.


## Import preconfiguration

###Install Apache2
```Shell
$ sudo apt-get install apache2
```

###Install PHP5
```Shell
$ sudo apt-get install php5 curl php5-curl libapache2-mod-php5
```

###Install Git
```Shell
$ sudo apt-get install git
```

###Install Composer
```Shell
$ sudo curl -s https://getcomposer.org/installer | php
$ sudo mv composer.phar /usr/local/bin/composer
$ composer
```



## Import
```Shell
$ sudo git clone https://JaimeJesusSerrano@bitbucket.org/JaimeJesusSerrano/symfony-contact-book.git
$ cd symfony-contact-book/
$ sudo composer install
```



## Import postconfiguration

### Modify .ini of php
```Shell
$ sudo nano /etc/php5/cli/php.ini
```
Add: date.timezone = Europe/Madrid
(Europe/Madrid for example)

### Modify permissions of folders
```Shell
$ cd symfony-contact-book/
$ sudo chmod 777 -R app/cache/
$ sudo chmod 777 -R app/logs/
$ sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs
```

### Check the installation
```Shell
$ cd symfony-contact-book/
$ php app/check.php
```

### Configure local server Apache2
```Shell
$ cd /etc/apache2/sites-available/
$ sudo nano symfony-contact-book.local.conf
```
And add the next:
```
<VirtualHost *:80>
    ServerName symfony-contact-book.local
    ServerAlias www.symfony-contact-book.local 
    DocumentRoot /var/www/symfony-contact-book/web #In my case: /home/user/projects/symfony-contact-book/web
    <Directory /var/www/symfony-contact-book/web>
        AllowOverride None
        Require all granted
        <IfModule mod_rewrite.c>
            Options -MultiViews
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ app_dev.php [QSA,L]
        </IfModule>
    </Directory>
    ErrorLog /var/log/apache2/project_error.log
    CustomLog /var/log/apache2/project_access.log combined
</VirtualHost>
```

### Enable site
```Shell
$ cd /etc/apache2/sites-enabled/
$ sudo a2dissite *
$ sudo a2ensite symfony-contact-book.local.conf
$ sudo service apache2 restart
```

### Add pathname
```Shell
$ sudo nano /etc/hosts
```
And add the next:
```
127.0.0.1    localhost symfony-contact-book.local	
```

### Configure database and install PDO drivers
```Shell
$ sudo apt-get install mysql-server-5.6
$ sudo apt-get install php5-mysql
$ sudo service mysql start
```

#### Configure UTF8
```Shell
$ sudo nano /etc/mysql/my.cnf
```
Search [mysqld] and add:
```
[mysqld]
collation-server = utf8_general_ci
character-set-server = utf8
```

#### Create database and tables
```Shell
$ cd symfony-contact-book/
$ php app/console doctrine:database:create
$ php app/console doctrine:schema:update --force
```



## Finally...
Go to http://symfony-contact-book.local/