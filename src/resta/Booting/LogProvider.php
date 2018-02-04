<?php

namespace Resta\Booting;

use Resta\ApplicationProvider;
use Resta\Logger\LoggerService;

class LogProvider extends ApplicationProvider {

    /**
     * @method boot
     * @return void
     */
    public function boot(){

        //route operations are the last part of the system run. In this section,
        //a route operation is passed through the url process and output is sent to the screen according to
        //the method file to be called by the application
        $this->app->bind('logger',function(){
            return LoggerService::class;
        });
    }

}