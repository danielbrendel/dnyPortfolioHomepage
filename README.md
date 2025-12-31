<h1 align="center">
    <img src="public/img/logo.png" alt="logo" width="100"/><br/>
    Daniel Brendel Portfolio App
</h1>

<p align="center">
    A personal portfolio application designed as a web desktop<br/>
    (C) 2019 - 2025 by Daniel Brendel<br/>
    Released under the MIT license
</p>

<p align="center">
    <img src="https://img.shields.io/badge/language-PHP-orange" alt="language-php"/>
    <img src="https://img.shields.io/badge/license-MIT-blue" alt="license-mit"/>
    <img src="https://img.shields.io/badge/nostalgic-YES-green" alt="nostalgic-yes"/>
</p>

## Information

**Version**: 1.0\
**Codename**: dnyPortfolioApp\
**Contact**: dbrendel1988(at)gmail(dot)com\
**GitHub**: https://github.com/danielbrendel/

## Description
This is my personal portfolio application designed as a [web desktop](https://en.wikipedia.org/wiki/Web_desktop). The GUI is designed to provide a maximum nostalgic experience. The project also features an applet system, so you can dynamically install and uninstall applets.

## Installation

You can install the project by following these steps:

1. Make sure you have PHP, MySQL and Composer installed on your system.
2. Clone the repository to your system using `git clone https://github.com/danielbrendel/dnyPortfolioApp`
3. Copy the `.env.example` to `.env` and adjust all necessary settings.
4. Run `composer install` in order to install all dependencies.
5. Run `php asatru migrate:fresh` to perform database migrations.
6. Fill in the database tables depending on your needs.
7. Launch the app via `php asatru serve`. The app will then be available via http://localhost:8000.

## Tech stack

The following technologies are used for this project.

| Technology  | Notes | Link |
| ------------- | ------------- | ------------- |
| PHP | General-purpose scripting language geared towards the web | [https://www.php.net](https://www.php.net) |
| Asatru PHP  | A lightweight PHP framework  | [https://www.asatru-php.com](https://www.asatru-php.com) |
| MariaDB  | Relational database management system  | [https://github.com/MariaDB/server](https://github.com/MariaDB/server) |
| Composer  | Dependency manager for PHP  | [https://getcomposer.org](https://getcomposer.org) |
| phpmailer/phpmailer | E-Mail sending library for PHP | [https://github.com/PHPMailer/PHPMailer](https://github.com/PHPMailer/PHPMailer) |
| nesbot/carbon  | API extension library for PHP DateTime  | [https://github.com/briannesbitt/Carbon](https://github.com/briannesbitt/Carbon) |
| npm | Package manager for JavaScript | [https://www.npmjs.com](https://www.npmjs.com) |
| webpack | A bundler for JavaScript and other assets | [https://github.com/webpack/webpack](https://github.com/webpack/webpack) |
| Bulma  | A modern CSS framework  | [https://bulma.io](https://bulma.io) |
| 98.css  | A design system for old UIs   | [https://github.com/jdan/98.css](https://github.com/jdan/98.css) |
| FontAwesome  | A popular icon library  | [https://fontawesome.com](https://fontawesome.com) |
