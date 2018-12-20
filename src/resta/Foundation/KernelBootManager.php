<?php

namespace Resta\Foundation;

use Bootstrapper\Manifest;

class KernelBootManager extends Manifest
{
    /**
     * @var array $makerList
     */
    protected $makerList=[];

    /**
     * @param $maker
     * @return mixed
     */
    protected function handle($maker)
    {
        // As a parameter, the maker variable comes as
        // the name of the list to be booted.
        if(isset($this->{$maker})){

            // we set this condition for users to boot the classes they want in the kernel groups.
            // in the manifesto, if the kernel groups method returns an class of arrays
            // then these classes will automatically join the kernel groups installation.
            if(property_exists($this,$makerExtend = $maker.'Extend')){

                // if the makerExtend value in the manifest is a method,
                // in this case, the method is executed instead of the object
                $checkMethodOrObjectForMakerExtend = (method_exists($this,$makerExtend))
                    ? $this->{$makerExtend}()
                    : $this->{$makerExtend};

                // get maker list as merged with checkMethodOrObjectForMakerExtend variable
                $this->makerList=array_merge($this->{$maker},$checkMethodOrObjectForMakerExtend);
            }
            else{
                $this->makerList=$this->{$maker};
            }
        }

        //revision maker
        $this->revisionMaker();

        //group name to boot
        return $this->makerList;
    }

    /**
     * @return void
     */
    private function revisionMaker()
    {
        if(count($this->makerList)){

            //We return to the boot list and perform a revision check.
            foreach ($this->makerList as $makerKey=>$makerValue){

                // the revision list is presented as a helper method to prevent
                // the listener application being booted from taking the entire listener individually.
                if(count($this->revision) && isset($this->revision[$makerValue])){
                    $this->makerList[$makerKey]=$this->revision[$makerValue];
                }
            }
        }
    }
}