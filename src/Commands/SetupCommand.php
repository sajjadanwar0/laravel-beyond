<?php

namespace Regnerisch\LaravelBeyond\Commands;

use Illuminate\Console\Command;
use Regnerisch\LaravelBeyond\Actions\ChangeComposerAutoloaderAction;
use Regnerisch\LaravelBeyond\Actions\DeleteAction;
use Regnerisch\LaravelBeyond\Actions\CopyAndRefactorFileAction;
use Regnerisch\LaravelBeyond\Actions\RefactorFileAction;

class SetupCommand extends Command
{
    protected $signature = 'beyond:setup {directory=src} {--no-delete}';

    protected $description = '';

    public function __construct(
        protected CopyAndRefactorFileAction      $copyAndRefactorFileAction,
        protected RefactorFileAction             $refactorFileAction,
        protected ChangeComposerAutoloaderAction $changeComposerAutoloaderAction,
        protected DeleteAction                   $deleteAction,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $noDelete = $this->option('--no-delete');

        // Console
        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Console/Kernel.php',
            base_path() . '/src/App/Console/Kernel.php'
        );

        // Exceptions
        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Exceptions/Handler.php',
            base_path() . '/src/App/Exceptions/Handler.php',
        );

        // Middlewares
        $this->moveMiddlewares();

        // Http Kernel
        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Http/Kernel.php',
            base_path() . '/src/App/HttpKernel.php',
            [
                'namespace App\Http;' => 'namespace App;',
                'use Illuminate\Foundation\Http\Kernel as HttpKernel;' => 'use Illuminate\Foundation\Http\Kernel;',
                'class Kernel extends HttpKernel' => 'class HttpKernel extends Kernel',
                '\App\Http\Middleware\\' => '\Support\Middlewares\\',
            ]
        );

        // Application
        beyond_copy_stub(
            'application.stub',
            base_path() . '/src/App/Application.php'
        );

        // Models
        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Models/User.php',
            base_path() . '/src/Domain/Users/Models/User.php',
            [
                'namespace App\Models;' => 'namespace Domain\Users\Models;',
            ]
        );

        // Providers
        $this->moveProviders();

        // Bootstrap
        $this->prepareBootstrap();

        // Rewrite configs
        $this->refactorFileAction->execute(
            base_path() . '/config/auth.php',
            [
                'App\Models\User::class' => 'Domain\Users\Models\User::class'
            ]
        );

        // Composer Autoloader
        $this->changeComposerAutoloaderAction->execute();

        if (!$noDelete) {
            // Delete app folder
            $this->deleteAction->execute(base_path() . '/app');
        }
    }

    protected function moveMiddlewares(): void
    {
        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Http/Middleware/Authenticate.php',
            base_path() . '/src/Support/Middlewares/Authenticate.php',
            [
                'namespace App\Http\Middleware;' => 'namespace Support\Middlewares;'
            ]
        );

        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Http/Middleware/EncryptCookies.php',
            base_path() . '/src/Support/Middlewares/EncryptCookies.php',
            [
                'namespace App\Http\Middleware;' => 'namespace Support\Middlewares;'
            ]
        );

        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Http/Middleware/PreventRequestsDuringMaintenance.php',
            base_path() . '/src/Support/Middlewares/PreventRequestsDuringMaintenance.php',
            [
                'namespace App\Http\Middleware;' => 'namespace Support\Middlewares;'
            ]
        );

        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Http/Middleware/RedirectIfAuthenticated.php',
            base_path() . '/src/Support/Middlewares/RedirectIfAuthenticated.php',
            [
                'namespace App\Http\Middleware;' => 'namespace Support\Middlewares;'
            ]
        );

        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Http/Middleware/TrimStrings.php',
            base_path() . '/src/Support/Middlewares/TrimStrings.php',
            [
                'namespace App\Http\Middleware;' => 'namespace Support\Middlewares;'
            ]
        );

        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Http/Middleware/TrustHosts.php',
            base_path() . '/src/Support/Middlewares/TrustHosts.php',
            [
                'namespace App\Http\Middleware;' => 'namespace Support\Middlewares;'
            ]
        );

        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Http/Middleware/TrustProxies.php',
            base_path() . '/src/Support/Middlewares/TrustProxies.php',
            [
                'namespace App\Http\Middleware;' => 'namespace Support\Middlewares;'
            ]
        );

        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Http/Middleware/VerifyCsrfToken.php',
            base_path() . '/src/Support/Middlewares/VerifyCsrfToken.php',
            [
                'namespace App\Http\Middleware;' => 'namespace Support\Middlewares;'
            ]
        );
    }

    protected function moveProviders(): void
    {
        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Providers/AppServiceProvider.php',
            base_path() . '/src/App/Providers/AppServiceProvider.php',
        );

        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Providers/AuthServiceProvider.php',
            base_path() . '/src/App/Providers/AuthServiceProvider.php',
        );

        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Providers/BroadcastServiceProvider.php',
            base_path() . '/src/App/Providers/BroadcastServiceProvider.php',
        );

        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Providers/EventServiceProvider.php',
            base_path() . '/src/App/Providers/EventServiceProvider.php',
        );

        $this->copyAndRefactorFileAction->execute(
            base_path() . '/app/Providers/RouteServiceProvider.php',
            base_path() . '/src/App/Providers/RouteServiceProvider.php',
        );
    }

    protected function prepareBootstrap(): void
    {
        $this->refactorFileAction->execute(
            base_path() . '/bootstrap/app.php',
            [
                'new Illuminate\Foundation\Application' => 'new App\Application',
                'App\Http\Kernel::class' => 'App\HttpKernel::class',
            ]
        );
    }
}