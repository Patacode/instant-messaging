# Instant messaging

This repo consist of a secure client/server **instant messaging** system project.

<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#build-and-run">Build and run</a></li>
      </ul>
    </li>
    <li><a href="#usage">Usage</a></li>
    <li><a href="#contribution">Contribution</a></li>
  </ol>
</details>

## About The Project

The project is a simple instant messaging web application where clients can communicate to
each other.

Different features are proposed through the website:

* An authentication system requiring that every user create an account before using the platform
* A contact system allowing clients to add themselves to their contact list
* A chat system allowing clients to chat with their contacts

Everything is done in real-time, so clients receive events in their browser without needing to reload it and their
views are updated dynamically.

Emphasis is placed on security, which means that every transaction on the website are secure, preserving integrity 
and confidentiality. 

### Built with

* [Laravel 9](https://laravel.com/)
* [Bootstrap 5](https://getbootstrap.com/)
* [Vue.js 2.6.12](https://vuejs.org/)
* [Node.js 18.1.0](https://nodejs.org/en/)
* [Pusher 7.0](https://pusher.com/)
* [PHP 8.0.2](https://www.php.net/)
* [JavaScript](https://javascript.info/)
* [HTML](https://www.w3.org/)
* [CSS](https://www.w3.org/)
* [SQLite 3.38.4](https://www.sqlite.org/)
* [Apache 2.4.53](https://httpd.apache.org/)
* [OpenSSL 1.1.1](https://www.openssl.org/)
* [Minica](https://github.com/jsha/minica)
* [Composer 2.3.5](https://getcomposer.org/)

## Getting started

To get the website up and running locally follow the steps below.

### Prerequisites

You will need to install and configure several tools for the application to
properly work.

* PHP and the web server Apache are needed for the project to be hosted and run correctly
* Laravel as well as Composer, Node.js and SQLite are needed by the application itself
* Minica is not required but can be used to simulate a Certificate Authority (CA)
* OpenSSL is needed in either way (whether minica is used or not)

We let you install all these dependencies yourself and adapt the installation
based on your OS. Moreover, please note that the following instructions have been
adapted to a Linux system, if you are using Windows or MacOS, please refer to
the alternative links.

Let now configure the proper files to correctly build and run the project.

The file **php.ini** (located under `/etc/php` on my arch system) must enable the following extensions:

```ini
extension=pdo_sqlite
extension=sqlite3
extension=openssl # if under windows
```

And the file **httpd.conf** (located under `/etc/httpd/conf` on my arch system) must enable the following modules:

```apacheconf
LoadModule mpm_event_module modules/mod_mpm_event.so
LoadModule authn_file_module modules/mod_authn_file.so
LoadModule authn_core_module modules/mod_authn_core.so
LoadModule authz_host_module modules/mod_authz_host.so
LoadModule authz_groupfile_module modules/mod_authz_groupfile.so
LoadModule authz_user_module modules/mod_authz_user.so
LoadModule authz_core_module modules/mod_authz_core.so
LoadModule access_compat_module modules/mod_access_compat.so
LoadModule auth_basic_module modules/mod_auth_basic.so
LoadModule socache_shmcb_module modules/mod_socache_shmcb.so
LoadModule reqtimeout_module modules/mod_reqtimeout.so
LoadModule include_module modules/mod_include.so
LoadModule filter_module modules/mod_filter.so
LoadModule mime_module modules/mod_mime.so
LoadModule log_config_module modules/mod_log_config.so
LoadModule env_module modules/mod_env.so
LoadModule headers_module modules/mod_headers.so
LoadModule setenvif_module modules/mod_setenvif.so
LoadModule version_module modules/mod_version.so
LoadModule slotmem_shm_module modules/mod_slotmem_shm.so
LoadModule ssl_module modules/mod_ssl.so
LoadModule unixd_module modules/mod_unixd.so
LoadModule status_module modules/mod_status.so
LoadModule autoindex_module modules/mod_autoindex.so
LoadModule negotiation_module modules/mod_negotiation.so
LoadModule dir_module modules/mod_dir.so
LoadModule actions_module modules/mod_actions.so
LoadModule userdir_module modules/mod_userdir.so
LoadModule alias_module modules/mod_alias.so
LoadModule rewrite_module modules/mod_rewrite.so
```

We also advise you to add the `index.php` value in the available directory indexes:
```apacheconf
<IfModule dir_module>
    DirectoryIndex index.html index.php
</IfModule>
```

Once these files have been updated, you can generate a private key and a self-signed certificate for the server. These elements
are needed by the web server to setup TLS protocol (thus enabling HTTPS).

```bash
openssl req -new -x509 -nodes -newkey rsa:2048 -keyout server.key -out server.crt -days 3650
```

Once generated you have to move them to the configuration directory of your web server. Here is an example on my system:

```bash
sudo mv server.* /etc/httpd/conf
```

Then you can set read-only permissions, and only allow the private key to be read by root:

```bash
sudo chmod 400 /etc/httpd/conf/server.key
sudo chmod 444 /etc/httpd/conf/server.crt
```

You also have the possibility to use **Minica** to simulate a CA (the **Go** language will be needed in this case). 

This simple script will generate a signed certificate for you automatically:

```bash
./minica --domains chat.com --ip-addresses 127.0.0.1
```

This method has the advantage of inhibiting the security warning popped by the web browser when accessing the 
website (due to self-signed certificate).

It will generate a self-signed certificate and private key for the CA if this is the first time you run it and will
also generate the same for the `chat.com` domain bind to the local ip address 127.0.0.1, with the certificate properly signed.

Just import the CA certificate into the thrust authorities of your web browser and move the signed certificate and private key
into the configuration folder of your web server.

---

Once these elements have been generated for the web server, lets create a virtual host for it to bind its 
ip address to the domain name `chat.com`. Just create a new virtual host including the following content:

```apacheconf
<VirtualHost *:80 *:443>
	SSLEngine on
	SSLCertificateFile "/path/to/web/server/conf/server.crt"
	SSLCertificateKeyFile "/path/to/web/server/conf/server.key"
	ServerAdmin webmaster@localhost
	DocumentRoot "/path/to/project/public" 
	<Directory "/path/to/project/public">
		Options +FollowSymlinks
        AllowOverride All
	    Require all granted
	</Directory>
	ServerName chat.com
	ErrorLog "/path/to/server/logs/error.log"
	CustomLog "/path/to/server/logs/access.log" combined
</VirtualHost>
```

The port `:443` has to be set on this virtual host if you are using a firewall to enable incoming TCP traffic.

Once all these steps done, you can finally restart your web server: 

```bash
sudo systemctl restart httpd
```

You are now ready to build and run the project.

**PS**: No script is provided to automatically configure these files because their location vary from
one system to another.

### Build and run

Before to build and run the project, clone it into a location accessible by your web server (e.g. under `/srv/http`):

```bash
git clone git@git.esi-bru.be:56080/instantmessaging.git
```

Change the rights and owners of the project following your needs. You can run the command below to be
sure every resources are accessible by the web server:

```bash
sudo chmod -R 777 chat/
```

Now, you can build the project with the provided bash script `setup`, located at root:

```bash
./setup
```

Please make sure that `DB_DATABASE` field in `.env` file at root, points to the right location before moving on (it should point to
the `db.sqlite` file under `/database`).

You will also need to fill in the `PUSHER_*` fields in the `.env` file with your own credentials. To obtain those credentials
you have to create a free account on [pusher](https://dashboard.pusher.com/accounts/sign_up) and create a new channel app by clicking
on the *Get started* button:

![pusherHomePage](/chat/resources/images/pusher.png)

Follow the steps in the dialog box to name your app and choose a preferred cluster location from the list. The tech stack settings are optional, they 
are just for the getting started page but can me change afterwards.

Once your channel is successfully created, you can go under the *App keys* section to obtain your credentials:

![credentials](/chat/resources/images/keys.png)

Just replace these fields in your `.env` file as bellow with your own keys to get pusher working:

```ini
PUSHER_APP_ID=xxxxxxx
PUSHER_APP_KEY=xxxxxxxxxxxxxxxxxxxx
PUSHER_APP_SECRET=xxxxxxxxxxxxxxxxxxxx
PUSHER_APP_CLUSTER=xx
```

Now that everything is set up, you can open your web browser and go to the address `https://chat.com` to initiate
the application and start using it.

![loginPage](/chat/resources/images/login_page.png)

Please note that an internet connection is required for the project to run properly due to the 
use of **Pusher** channels.

## Usage

Here is a simple demo showing you how the project work:

![demoApp](/chat/resources/gif/demo.gif)

## Contribution

This little project has been made by:

* Maximilien Ballesteros
