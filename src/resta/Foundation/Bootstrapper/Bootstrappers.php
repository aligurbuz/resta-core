<?php

namespace Resta\Foundation\Bootstrapper;

use Resta\GlobalLoaders\ApplicationPreLoader;

class Bootstrappers
{
    /**
     * @var array $stack
     */
    protected $stack=[];

    /**
     * @var $concrete null
     */
    protected $concrete;

    /**
     * @var $bootstrapper null
     */
    protected $bootstrapper;

    /**
     * @var $pusher array
     */
    protected $pusher=array();

    /**
     * @var $pusherStacks array
     */
    protected $pusherStacks=array();

    /**
     * @var bootstrappers array
     */
    protected $bootstrappers=[
        'originGroups',
        'consoleGroups',
        'middlewareGroups',
        'reflectionGroups'
    ];

    /**
     * Bootstrappers constructor.
     * @param null $concrete
     * @param array $pusher
     * @param null $bootstrapper
     */
    public function __construct($concrete=null,$pusher=array(),$bootstrapper=null)
    {
        //if the user sets the bootstrapper variable to true,
        //we do not do anything.
        if($bootstrapper===true){
            throw new \LogicException('bootstrapper is not available');
        }


        //The concrete object is the callback class itself that is sent to this class.
        //Once the concrete object has been assigned, we run the bootstrappers sequence
        //and include it in the application.
        //The pusher and bootstrapper variables can be sent by the user.
        $this->concrete     = $concrete;
        $this->pusher       = $pusher;
        $this->bootstrapper = $bootstrapper;

        //we boot the initial instance for the application.
        if($this->ifExistPusher()) (new ApplicationPreLoader($this->concrete))->handle();

        //call bootstrapper process
        $this->callBootstrapperProcess();

        // If you do not have a special pusher list,
        // we are peeling.
        if($this->ifExistPusher()){
            $this->peelings();
        }
    }

    /**
     * @param bool $bootstrapper
     * @return mixed
     */
    private function getBootstrappers($bootstrapper=false)
    {
        //if a bootstrapper variable is sent as false to the installer object,
        //the bootstrapper will be assigned as false this variable directly.
        if(false===$this->bootstrapper){
            $bootstrapper=$this->bootstrapper;
        }

        //if the value to be sent as the second parameter for this method is true,
        //the values ​​in the first parameter of the method will be combined with the bootstrappers list.
        if($bootstrapper && $this->ifExistPusher()){
            $bootstrapStack=array_merge($this->pusher,$this->bootstrappers);
        }

        //Bootstrap list join check is done and list is returned.
        return (isset($bootstrapStack)) ? $bootstrapStack : $this->pusher;
    }

    /**
     * @param array $customBootstrapers
     */
    public function callBootstrapperProcess($customBootstrapers=[])
    {
        // here we check that a special bootstrappers list will work and we identify the onion identifier.
        // we are peeling onion class by classifying onion class.
        $this->getBootstrappersStack($customBootstrapers);

        //We run the bootstrap list by callback with the object specified for the content respectively.
        foreach($this->stack['getBootstrappers'] as $bootstrapper){

            // if the callback data is different from the application kernel,
            // we will pass it to the pusher control for a special use.
            $this->ifExistPusher(function($call) use($bootstrapper){
                call_user_func_array([$this->concrete,$call],[$bootstrapper,$this,$this->stack['onionIdentifier']]);
            });
        }
    }

    /**
     * @method peelings
     * @return mixed|void
     */
    private function peelings()
    {
        //if there are peelings
        if(isset(core()->peelings)){

            // We send the peelings property to
            // the bootstrapperPeelOnion class.
            $peelings=core()->peelings;
            pos($peelings)->onionRun($peelings);
        }
    }

    /**
     * @param null $callback
     * @return bool
     */
    private function ifExistPusher($callback=null)
    {
        // With the pusher event,
        // we are running a boot on condition
        // that it accepts an object and array logic belonging
        // to this class that is run outside the kernel system.
        $checkPusher=(count($this->pusher)=='0') ? true : false;

        //pusher is run if there is no callback.
        if(!is_callable($callback)){
            return $checkPusher;
        }

        // we check the presence of
        // the array variable we checked to pusher operation.
        if(!$checkPusher) return $this->pusherHandle();

        // Without the pusher,
        // the kernel bootstrapper feature of this system is the callback data.
        return call_user_func_array($callback,['callBootstrapperProcess']);
    }

    /**
     * @return void
     */
    private function pusherHandle()
    {
        //pusher stack
        $pusherStack=[];

        // we push the collected pusher data to
        // the pusherStacks data with the concrete object.
        foreach ($this->pusher as $pusher){
            $pusherStack[]=$this->concrete->{$pusher}();
        }

        //pusherHandle key of the pusherStack array
        $this->pusherStacks['pusherHandle']=$pusherStack;
    }

    /**
     * @param $customBootstrapers
     */
    private function getBootstrappersStack($customBootstrapers)
    {
        // here we check that a special bootstrappers list will work and we identify the onion identifier.
        // we are peeling onion class by classifying onion class.
        $customBootstrapersCount            = count($customBootstrapers);
        $getBootstrappers                   = $this->getBootstrappers(true);
        $this->stack['getBootstrappers']    = ($customBootstrapersCount) ? $customBootstrapers : $getBootstrappers;
        $this->stack['onionIdentifier']     = ($customBootstrapersCount) ? false : true;
    }

    /**
     * @return array|mixed
     */
    public function getPusher()
    {
        // a public method
        // for the pushers collected.
        if(count($this->pusherStacks)){
            return $this->pusherStacks['pusherHandle'];
        }
        return [];
    }
}