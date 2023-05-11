# BUBUGET API

## Server Requirements

<b>PHP</b> - version 7.4 or higher is required.

<b>Composer</b> - install composer for Windows. <a target="_blank" href="https://getcomposer.org/Composer-Setup.exe">Click Here to Download.</a>

## Setup

Rename `env` to `.env`, hilangkan command atau tekan Ctrl + / untuk CI_ENVIRONMENT dan value lain yang dibutuhkan.

import `bubuget.sql` ke phpmyadmin atau RDBMS masing-masing.

## Installation

### Update Composer

```
composer update
```

### Install Dependencies

```
composer install
```

### Start the application

default host (localhost)

```
php spark serve
```

custom host (apabila default host tidak bisa)

```
php spark serve --host 127.0.0.1
```

buka <a target="_blank" href="http://localhost:8080">localhost:8080</a> di browser
