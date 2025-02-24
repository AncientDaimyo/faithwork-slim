<?php

use Slim\App;

return function (App $app) {
    $app->get('/', function ($request, $response, $args) {
        $response = $response->withStatus(302)->withHeader('Location', '/api/ui');
        return $response;
    });



    $app->group('/api', function ($app) {
        $app->get('/health', \App\Shared\Infrastructure\Controller\ServiceController::class . ':health');

        $app->get('/doc', function ($request, $response, $args) {
            $openapi = \OpenApi\Generator::scan([__DIR__ . '/../src']);

            $response = $response->withHeader('Content-type', 'application/json');
            $response->getBody()->write($openapi->toJson());
            return $response;
        });



        $app->get('/ui', function ($request, $response, $args) {
            $response = $response->withHeader('Content-type', 'text/html');
            $response->getBody()->write(file_get_contents(__DIR__ . '/../public/dist/index.html'));
            return $response;
        });

        $app->group('/admin', function ($app) {
            $app->group('/migrations', function ($app) {
                $app->get('/up', \App\Admin\Infrastructure\Controller\AdminMigrationsController::class . ':up');
                $app->get('/down', \App\Admin\Infrastructure\Controller\AdminMigrationsController::class . ':down');
            });
        });
      

        $app->group('/product', function ($app) {
            $app->get('/products', \App\Product\Infrastructure\Controller\ProductController::class . ':getProducts');
            $app->get('/products/{id}', \App\Product\Infrastructure\Controller\ProductController::class . ':getProduct');
        });

        $app->group('/customer', function ($app) {
            $app->get('/account/{id}', \App\Customer\Infrastructure\Controller\CustomerController::class . ':getAccountData');
            $app->put('/account/{id}', \App\Customer\Infrastructure\Controller\CustomerController::class . ':updateAccountData');
            $app->delete('/account/{id}', \App\Customer\Infrastructure\Controller\CustomerController::class . ':deleteAccount');
        });

        $app->group('/auth', function ($app) {
            $app->post('/login', \App\Auth\Infrastructure\Controller\AuthController::class . ':login');
            $app->post('/register', \App\Auth\Infrastructure\Controller\AuthController::class . ':register');
            $app->post('/logout', \App\Auth\Infrastructure\Controller\AuthController::class . ':logout');
            $app->post('/refresh', \App\Auth\Infrastructure\Controller\AuthController::class . ':refresh');
        });

        $app->group('/order', function ($app) {
            $app->get('/orders', \App\Order\Infrastructure\Controller\OrderController::class . ':getOrders');
            $app->get('/orders/{id}', \App\Order\Infrastructure\Controller\OrderController::class . ':getOrder');
            $app->post('/orders', \App\Order\Infrastructure\Controller\OrderController::class . ':createOrder');
            $app->put('/orders/{id}', \App\Order\Infrastructure\Controller\OrderController::class . ':updateOrder');
            $app->delete('/orders/{id}', \App\Order\Infrastructure\Controller\OrderController::class . ':deleteOrder');
        });

        $app->get('/{path}', function ($request, $response, $args) {
            $path = explode('.', (string)$request->getAttribute('path'));
            
            if (count($path) != 2) {
                $response = $response->withStatus(404);
                return $response;
            }

            switch ($path[1]) {
                case 'css':
                    $response = $response->withHeader('Content-type', 'text/css');
                    try {
                        $response->getBody()->write(file_get_contents(__DIR__ . '/../public/dist/' . $path[0] . '.css'));
                        $response = $response->withStatus(200);
                    } catch (\Exception $e) {
                        $response = $response->withStatus(404);
                    }
                    break;
                case 'js':
                    $response = $response->withHeader('Content-type', 'application/javascript');
                    try {
                        $response->getBody()->write(file_get_contents(__DIR__ . '/../public/dist/' . $path[0] . '.js'));
                        $response = $response->withStatus(200);
                    } catch (\Exception $e) {
                        $response = $response->withStatus(404);
                    }
                    break;
                case 'png':
                    $response = $response->withHeader('Content-type', 'image/png');
                    try {
                        $response->getBody()->write(file_get_contents(__DIR__ . '/../public/dist/' . $path[0] . '.png'));
                        $response = $response->withStatus(200);
                    } catch (\Exception $e) {
                        $response = $response->withStatus(404);
                    }
                    break;
                default:
                    $response = $response->withStatus(404);
            }
            return $response;
        });
    });
};
