<?php

namespace Resta\Logger;

use Resta\ApplicationProvider;
use Resta\StaticPathModel;

class LoggerService extends ApplicationProvider {

    public function handle(){

        //We are getting the path to
        //the service log file in the project's version directory.
        $appBase=$this->makeBind(StaticPathModel::appServiceLog());

        //The service log class must have an adapter object.
        $logAdapter=$appBase->adapter;

        //in order to customize the adapter property contained in this file,
        //we can process it in a method so that we can specify a log adapter property
        //that is bounded by the state.
        if(method_exists($appBase,'adapter')){
            $logAdapter=$appBase->adapter();
        }

        //we send the resulting adapter property as a reference to the bind automatic instance class.
        $this->singleton()->loggerGlobalInstance->adapterProcess($appBase,$logAdapter);
    }

    /**
     * @param $printer
     * @return mixed
     */
    public function logHandler($printer){

        //we get the log object that was previously assigned.
        $log=$this->singleton()->log;

        //this object is obtained directly as an array and specifies
        //the adapter value for the first key log. The value of the directory stores
        //the instance value of the service log class. From there,
        //we call the method specified by the adapter in the service log class
        //and log the application in the customized mode for the application.
        //The service log class uses the monolog class.
        if(method_exists($base=current($log),$adapter=key($log))){
            call_user_func_array([$base,$adapter],[$printer]);
        }

        //printer back
        return $printer;
    }

}