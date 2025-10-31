<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
// create a request with an authenticated user if possible
$request = Illuminate\Http\Request::create('/admin/analytics/users/fragment', 'GET');
// attempt to set an admin user id in session (if app uses session guard) - skip, we'll call controller directly from container
$controller = $app->make(App\Http\Controllers\Admin\AnalyticsController::class);
// Ensure facades work in this script
Illuminate\Support\Facades\Facade::setFacadeApplication($app);
try {
    $resp = $controller->usersFragment($request);
    if ($resp instanceof Illuminate\Contracts\Support\Renderable) {
        echo $resp->render();
    } elseif ($resp instanceof Illuminate\Http\Response) {
        echo $resp->getContent();
    } else {
        var_export($resp);
    }
} catch (Throwable $e) {
    echo get_class($e)." - ".$e->getMessage()."\n".$e->getTraceAsString();
}
