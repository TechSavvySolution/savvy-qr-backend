protected $routeMiddleware = [
    'custom.jwt' => \App\Http\Middleware\CustomJwtAuth::class,
];
