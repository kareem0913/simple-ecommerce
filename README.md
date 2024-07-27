<p align="center">
    <a href="https://laravel.com" target="_blank">
        <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
    </a>
</p>

<p align="center">
    <a href="https://github.com/laravel/framework/actions">
        <img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status">
    </a>
    <a href="https://packagist.org/packages/laravel/framework">
        <img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/laravel/framework">
        <img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/laravel/framework">
        <img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">
    </a>
</p>

## About This Project
This project is a simple e-commerce application built with Laravel. It includes features for managing products and categories, with functionality for CRUD operations, file uploads, and pagination.

## Features

- User Registration with jwt for api , and built in laravel for web view.
- User Login and Logout
- CRUD operations for products.
- RESTful API endpoints for all features
- CSRF protection for web interfaces
- Input sanitization and validation to prevent SQL injection and XSS attacks.
- usign laravel factories and seeders for insert fake products and category.
- test endpoint webhooks with <a href="https://webhook.site" > webhooks </a>
- api documentaion with swagger.

## Requirements

- PHP >= 10
- Composer
- Node.js & npm
- A web server (e.g., Apache, Nginx)
- A database (e.g., MySQL, PostgreSQL)

  ## Important Notice
   this project handle webhooks endpoint with webhooks site so you must open this site<a href="https://webhook.site" > webhooks </a> and get your unique url
    , for example like `https://webhook.site/fb8b592c-73e1-40cf-a393-ed0468523589` and you must add your unique url in .env file with this variable `WEB_HOOKS_URL`


### Installation and Runing the project

1. **Clone the repository:** 
   ```bash
   git clone <git@github.com:kareem0913/simple-ecommerce.git>
   
2. **Install dependencies:**

   ```bash
    composer install
    npm insatll
   
4. **Configure your `.env` file with necessary environment variables, and make sure about env variable 
   `APP_URL=http://localhost:8000` or your online server**

6. **generate key for laravel app:**
   
   ```bash
   php artisan key:generate
   
7. **generate jwt secret key in `.env` file:**

   ```bash
   php artisan jwt:secret
   
8. **create storage path link:**

   ```bash
    php artisan storage:link
     
9. **Run database migrations:**
   
    ```bash
     php artisan migrate
    
10. **Run database seeders:**
    
    ```bash
     php artisan db:seed
    
11. **Start the development server:**
    
    ```bash
    php artisan serve
    npm run dev
    
12.**you can access swagger api endpoint with `http://localhost:8000/api/documentation`**
