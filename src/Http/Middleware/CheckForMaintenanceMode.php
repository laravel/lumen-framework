<?php namespace Laravel\Lumen\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory as View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckForMaintenanceMode
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The view factory instance.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;

    /**
     * Create a new check for maintenance mode instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Illuminate\Contracts\View\Factory           $view
     *
     * @return void
     */
    public function __construct(Application $app, View $view)
    {
        $this->app = $app;
        $this->view = $view;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->app->isDownForMaintenance()) {
            return new Response($this->maintenance(), 503);
        }

        return $next($request);
    }

    public function maintenance()
    {
        return "<html>
            <head>
                <title>Maintenance.</title>

                <link href='//fonts.googleapis.com/css?family=Lato:100' rel='stylesheet' type='text/css'>

                <style>
                    body {
                        margin: 0;
                        padding: 0;
                        width: 100%;
                        height: 100%;
                        color: #B0BEC5;
                        display: table;
                        font-weight: 100;
                        font-family: 'Lato';
                    }

                    .container {
                        text-align: center;
                        display: table-cell;
                        vertical-align: middle;
                    }

                    .content {
                        text-align: center;
                        display: inline-block;
                    }

                    .title {
                        font-size: 96px;
                        margin-bottom: 40px;
                    }

                    .quote {
                        font-size: 24px;
                    }
                </style>
            </head>
            <body>
                <div class=\"container\">
                    <div class=\"content\">
                        <div class=\"title\">Maintenance, back soon.</div>
                    </div>
                </div>
            </body>
        </html>
        ";
    }
}
