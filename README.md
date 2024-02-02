# PHP Router Example

This is a simple PHP router class that can be used to define routes for web applications.

## Usage

1. **Installation**

    First, you need to include the `Route.php` file in your project. You can do this by copying the content of the `Route.php` file into your project or by using Composer.

    ```bash
    composer require your-vendor/your-project
    ```

2. **Basic Usage**

    ```php
    <?php
    require_once 'vendor/autoload.php'; // Adjust this based on your project's structure

    use Core\Route;

    $router = new Route();

    $router->get('/', function () {
        echo "Hello, World!";
    });

    $router->get('/about', function () {
        echo "About Us Page";
    });

    $router->dispatch();
    ```

3. **Defining Routes**

    ```php
    $router->get('/products/:id', function ($id) {
        echo "Product ID: $id";
    })->name('product');

    $router->get('/categories/:slug', 'CategoryController@show')->name('category.show');
    ```

4. **URL Generation**

    ```php
    $productUrl = $router->url('product', ['id' => 123]);
    echo "Product URL: $productUrl";
    ```

5. **Subdomain Routing**

    ```php
    $router->subdomain('admin', function () {
        Route::get('/dashboard', 'AdminController@dashboard')->name('admin.dashboard');
    });
    ```

6. **Read More**

    For more details and advanced usage, please refer to the documentation in the `docs` directory.


