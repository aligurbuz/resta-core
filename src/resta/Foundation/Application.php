<?php

namespace Resta\Foundation;

use Resta\Support\Str;
use Resta\Config\Config;
use Resta\Traits\ApplicationPath;
use Illuminate\Support\Collection;
use Resta\Support\ClosureDispatcher;
use Resta\Contracts\ApplicationContracts;
use Resta\Contracts\ConfigProviderContracts;
use Resta\Middleware\MiddlewareKernelProvider;
use Resta\Environment\EnvironmentConfiguration;
use Resta\Contracts\ApplicationHelpersContracts;
use Resta\Foundation\Bootstrapper\Bootstrappers;
use Resta\Foundation\Bootstrapper\BootFireCallback;
use Resta\Foundation\Bootstrapper\KernelManifestManager;

class Application extends Kernel implements ApplicationContracts,ApplicationHelpersContracts
{
    //get app paths
    use ApplicationPath;

    /**
     * The Resta api designer version.
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * The Laravel framework version.
     *
     * @var string
     */
    protected const LOADBOOTSTRAPPERS = 'loadBootstrappers';

    /**
     * @var bool $console
     */
    protected $console;

    /**
     * Application constructor.
     *
     * @param bool $console
     */
    public function __construct($console=false)
    {
        // get console status for cli
        $this->console = $console;

        // the bootstrapper method is the initial process
        // that runs the individual methods that the application initiated.
        new Bootstrappers($this);
    }

    /**
     * kernel boot manager method
     *
     * @param null $boot
     * @param null $maker
     * @return mixed
     */
    protected function bootFire($boot=null,$maker=null)
    {
        // we can refer to this method
        // because we can boot classes in the middleware or bootstrapper array.
        if($boot===null && $maker!==null){

            // we create kernel bootstrapping objects
            // that can be changed by you with the closure dispatcher method.
            return ClosureDispatcher::bind($this->resolve(KernelManifestManager::class))
                ->call(function() use ($maker){
                    return $this->handle($maker);
            });
        }

        // the boot method to be executed can be specified by the user.
        // we use this method to know how to customize it.
        return forward_static_call_array([array_pop($boot),self::LOADBOOTSTRAPPERS],[$boot]);
    }

    /**
     * application bootstrappers
     *
     * @method bootstrappers
     * @param $app \Resta\Contracts\ApplicationContracts
     * @param $strappers bootstrappers
     */
    protected function bootstrappers($app,$strappers)
    {
        //The boot method to be executed can be specified by the user.
        //We use this method to know how to customize it.
        BootFireCallback::setBootFire([$app,$strappers],function($boot){

            //kernel boots run and service container
            //makeBuild for service Container
            $this->bootFire($boot);
        });
    }

    /**
     * application call bootstrapper process
     *
     * @param $group
     * @param $booting
     */
    public function callBootstrapperProcess($group,$booting,$onion=true)
    {
        if($onion){

            // we will implement a special onion method here and
            // pass our bootstraper classes through this method.
            // Our goal here is to implement the middleware layer correctly.
            $this->resolve(MiddlewareKernelProvider::class)->onionBoot([$group,$booting],function() use($group){
                $this->bootstrappers($this,$group);
            });

            return false;
        }

        //system booting for app
        //pre-loaders are the most necessary classes for the system.
        $this->bootstrappers($this,$group);
    }

    /**
     * check if the object in binding is available
     *
     * @param $object
     */
    public function checkBindings($object)
    {
        // the booted objects are saved to the kernel.
        // this method checks whether these objects are registered.
        return (isset($this['bindings'],$this['bindings'][$object]));
    }

    /**
     * get kernel command list
     *
     * @return array
     */
    public function commandList()
    {
        //get command list from kernel
        return $this->commandList;
    }

    /**
     * get configuration values
     *
     * @param null $config
     * @return mixed|void
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function config($config=null)
    {
        if($this->checkBindings(__FUNCTION__)){
            return Config::make($config)->get();
        }

        // if the environment is not booted,
        // it creates a direct missing ring
        // and we have to reinstall the environment to remove it.
        $this->loadIfNotExistBoot([__FUNCTION__]);
        return $this->config($config);
    }

    /**
     * console kernel object
     *
     * @return bool|mixed|null
     */
    public function console()
    {
        // controlling the console object is
        // intended to make sure that the kernel bootstrap classes do not work.
        return $this->console;
    }

    /**
     * get core path
     *
     * @return mixed
     */
    public function corePath()
    {
        // get the directory
        // where kernel files are running to the kernel object.
        return core()->corePath ?: null;
    }

    /**
     * detect environment according to application key
     *
     * @return mixed|string
     */
    public function detectEnvironmentForApplicationKey()
    {
        if(isset($this['applicationKey'])){

            // application key, but if it has a null value
            // then we move the environment value to the production environment.
            return ($this['applicationKey']===null) ? 'production' : $this->environment();
        }

        return $this->environment();
    }

    /**
     * get environment variables
     *
     * @param array $environment
     * @return mixed|string
     */
    public function environment($environment=array())
    {
        if($this->checkBindings(__FUNCTION__)){

            $arguments = (isset(func_get_args()[0]))
                ? func_get_args()[0] : func_get_args();

            return EnvironmentConfiguration::environment(
                $arguments,$this['environmentVariables']
            );
        }

        // if the environment is not booted,
        // it creates a direct missing ring
        // and we have to reinstall the environment to remove it.
        $this->loadIfNotExistBoot([__FUNCTION__]);
        return $this->environment();
    }

    /**
     * handle application
     *
     * @return void|mixed
     */
    public function handle()
    {
        // this is the main calling place of your application.
        // if you come via http, the kernel response value is appraised.
        // if you come via console, the kernel console value is appraised.
        return ($this->console()) ? null : $this->kernel->response;
    }

    /**
     * Determine if application locale is the given locale.
     *
     * @return bool
     */
    public function isLocale()
    {
        //check environment for local
        return $this->environment() === 'local';
    }

    /**
     * check if the request is console
     *
     * @return bool
     */
    public function runningInConsole()
    {
        //Determine if the application is running in the console.
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    /**
     * get all kernel bootstrapper groups keys
     *
     * @return array
     */
    public function kernelGroupKeys()
    {
        $properties = [];

        // with the help of reflection instance,
        // we get the kernel properties extended with the application object.
        foreach ($this['reflection']($this)->getProperties() as $property){
            $properties[] = $property->getName();
        }

        // we get the names of
        // the kernel properties ended with groups through the Collection class.
        [$groups] = Collection::make($properties)->partition(function($properties){
           return Str::endsWith($properties,'Groups');
        });

        //as a result, kernel groups are being returned.
        return array_values($groups->all());
    }

    /**
     * kernel groups name lists
     *
     * @return array|void
     */
    public function kernelGroupList()
    {
        $list = [];

        //get kernel group names with manifest method
        foreach ($this->kernelGroupKeys() as $groupKey){
            $list = array_merge($list,$this->manifest($groupKey));
        }

        return $list;
    }

    /**
     * customer configuration loader for core
     *
     * @param callable $callback
     * @return mixed
     */
    public function loadConfig(callable $callback)
    {
        // it adds the values in path data specified
        // by callback to the configuration values.
        if($this['config'] instanceof ConfigProviderContracts){

            //set your path for config loader
            return tap($this['config'],function(ConfigProviderContracts $config) use($callback) {
                return $config->setConfig(call_user_func($callback));
            });
        }

        //set config instance exception for application
        exception()->unexpectedValue('config instance is not loaded for application container');
    }

    /**
     * load if not exist boot
     *
     * @param array $loaders
     */
    public function loadIfNotExistBoot($loaders=array())
    {
        //get kernel group list from application
        $kernelGroupList = $this->kernelGroupList();

        foreach ($loaders as $loader){

            // if a service needs another boot service,
            // the service is directly installed here and the service needs are resolved.
            if(isset($kernelGroupList[$loader]) && $this->checkBindings($loader)===false){

                //with the boot loader kernel,we get the boot loader method.
                $this['closureBootLoader']->call(function() use($loader,$kernelGroupList) {
                    $this->bootstrapper = $kernelGroupList[$loader];
                    return $this->boot();
                });
            }
        }
    }

    /**
     * get kernel maker from manifest
     *
     * @param $maker
     * @return mixed
     */
    public function manifest($maker)
    {
        //kernel manifest bootstrapper
        return $this->bootFire(null,$maker);
    }

    /**
     * get service providers
     *
     * @return array
     */
    public function serviceProviders()
    {
        //get project providers from config kernel
        $providers = (is_array(config('kernel.providers')))
            ? config('kernel.providers')
            : [];

        //core kernel providers and project providers have been merged
        return array_merge($this->manifest('providers'),$providers);
    }

    /**
     * application structure directory paths changing
     *
     * @param null $name
     * @param null $path
     * @return mixed|void
     */
    public function setPaths($name=null,$path=null)
    {
        // save the globally identified paths to
        // the global container object of the resta.
        if($name!==null && $path!==null && file_exists($path)){
            $this->register('paths',$name,$path);
        }
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        //get resta application version number
        return static::VERSION;
    }
}