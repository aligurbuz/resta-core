<?php

namespace Resta\Foundation\Bootstrapper;

use Src\Manifest;
use Resta\Support\Arr;
use Resta\Support\ClosureDispatcher;

class KernelManifestManager
{
    /**
     * @var array $makerList
     */
    protected $makerList=[];

    /**
     * @var $manifest
     */
    protected $manifest;

    /**
     * KernelBootManager constructor.
     */
    public function __construct()
    {
        //default manifest is application
        $this->manifest = app();

        // if there is manifest propery in the resta
        // in this case,manifest property is manifest class
        if(isset(core()->manifest)){
            $this->manifest = $this->manifest->makeBind(Manifest::class);
        }

        //closure dispatcher for manifest property
        $this->manifest = ClosureDispatcher::bind($this->manifest);
    }

    /**
     * get makerList from property
     *
     * @return array
     */
    public function getMakerList()
    {
        //get makerList property
        return $this->makerList;
    }

    /**
     * @param $maker
     * @return mixed
     */
    protected function handle($maker)
    {
        $app = clone $this;

        return $this->manifest->call(function() use ($maker,$app){

            // As a parameter, the maker variable comes as
            // the name of the list to be booted.
            if(isset($this->{$maker})){

                //get default maker list
                $app->setMakerList($this->{$maker});

                // we set this condition for users to boot the classes they want in the kernel groups.
                // in the manifesto, if the kernel groups method returns an class of arrays
                // then these classes will automatically join the kernel groups installation.
                if(isset($this->app) && isset($this->app[$maker]) && is_array($this->app[$maker])){

                    // if the makerExtend value in the manifest is a method,
                    // in this case, the method is executed instead of the object
                    $checkMethodOrObjectForMakerExtend =
                        (method_exists($this,$maker) && is_array($this->{$maker}()))
                            ? $this->{$maker}()
                            : $this->app[$maker];

                    // get maker list as merged with checkMethodOrObjectForMakerExtend variable
                    $app->setMakerList(array_merge($this->{$maker},$checkMethodOrObjectForMakerExtend));
                }
            }

            // revision maker
            // group name to boot
            $app->revisionMaker($this->revision);
            return $app->getMakerList();
        });

    }

    /**
     * check revision by manifest
     *
     * @param $revision
     */
    public function revisionMaker($revision)
    {
        if(is_array($revision) && count($this->makerList)){

            //We return to the boot list and perform a revision check.
            foreach ($this->makerList as $makerKey=>$makerValue){

                // the revision list is presented as a helper method to prevent
                // the listener application being booted from taking the entire listener individually.
                if(count($revision) && isset($revision[$makerKey])){
                    $this->makerList[$makerKey] = $revision[$makerKey];
                }
            }
        }
    }

    /**
     * set a value for makerList property
     *
     * @param $maker
     */
    public function setMakerList($maker)
    {
        //set makerList property
        $this->makerList = $maker;
    }
}