<?php

namespace Resta\Console\Source\Controller;

use Resta\StaticPathList;
use Resta\Utils;
use Resta\StaticPathModel;
use Resta\Console\ConsoleOutputter;
use Resta\Console\ConsoleListAccessor;

class Controller extends ConsoleOutputter {

    use ConsoleListAccessor;

    /**
     * @var $type
     */
    public $type='controller';

    /**
     * @var array
     */
    protected $runnableMethods = [
        'create'    => 'Creates a controller',
        'rename'    => 'rename controller'
    ];

    /**
     * @var bool
     */
    protected $projectStatus = true;

    /**
     * @var array
     */
    protected $commandRule=[
        'create'    => ['controller'],
        'rename'    => ['controller','rename']
    ];

    /**
     * @method create
     * @return mixed
     */
    public function create(){

        $controller                     = $this->argument['controller'];
        $resourceInController           = $this->argument['resourceInController'] = StaticPathList::$resourceInController;
        $configurationInController      = $this->argument['configurationInController'] = StaticPathList::$configurationInController;

        //Processes related to argument variables via console.
        $this->argument['methodPrefix']         = StaticPathModel::$methodPrefix;
        $this->directory['endpoint']            = $this->controller().'/'.$controller.''.StaticPathList::$controllerBundleName;
        $this->directory['resource']            = $this->directory['endpoint'].'/'.$resourceInController;
        $this->directory['test']                = $this->directory['endpoint'].'/Test';
        $this->directory['contract']           = $this->directory['endpoint'].'/Contract';
        $this->directory['configuration']       = $this->directory['endpoint'].'/'.$configurationInController;
        $this->argument['controllerNamespace']  = Utils::getNamespace($this->directory['endpoint']);
        $this->argument['serviceClass']         = $controller;
        $this->argument['callClassPrefix']      = StaticPathModel::$callClassPrefix;

        // with the directory operation,
        // we get to the service directory, which is called the controller.
        $this->file->makeDirectory($this);


        // we process the processes related to file creation operations.
        // and then create files related to the touch method of the file object as it is in the directory process.
        $this->touch['service/endpoint']        = $this->directory['endpoint'].'/'.$this->argument['serviceClass'].''. $this->argument['callClassPrefix'].'.php';
        $this->touch['service/acl']             = $this->directory['resource'].'/'.$this->argument['serviceClass'].'AclManagement.php';
        $this->touch['service/app']             = $this->directory['endpoint'].'/App.php';
        $this->touch['service/developer']       = $this->directory['configuration'].'/Developer.php';
        $this->touch['service/conf']            = $this->directory['configuration'].'/ServiceConf.php';
        $this->touch['service/dummy']           = $this->directory['configuration'].'/Dummy.yaml';
        $this->touch['service/doc']             = $this->directory['configuration'].'/Doc.yaml';
        $this->touch['service/readme']          = $this->directory['endpoint'].'/README.md';
        $this->touch['service/testIndex']       = $this->directory['test'].'/index.html';
        $this->touch['service/contractIndex']  = $this->directory['contract'].'/index.html';

        $this->file->touch($this,[
            'stub'=>'Controller_Create'
        ]);

        $this->docUpdate();
        
        // and as a result we print the result on the console screen.
        echo $this->classical(' > Controller called as "'.$controller.'" has been successfully created in the '.app()->namespace()->call().'');

    }

    /**
     * @return mixed
     *
     */
    public function rename()
    {
        $path = app()->path()->controller().'/'.$this->argument['controller'].''.StaticPathList::$controllerBundleName;

        if(file_exists($path)){

            $newPathName = str_replace($this->argument['controller'],$this->argument['rename'],$path);


            rename($path,$newPathName);

            $getAllFiles = Utils::getAllFilesInDirectory($newPathName);

            $getPathWithPhpExtensions = Utils::getPathWithPhpExtension($getAllFiles,$newPathName);

            foreach ($getPathWithPhpExtensions as $getPathWithPhpExtension){

                $withoutFullPath = str_replace($newPathName,"",$getPathWithPhpExtension);

                $newName = $newPathName."".preg_replace("((.*)".$this->argument['controller'].")", "$1".$this->argument['rename'], $withoutFullPath);

                rename($getPathWithPhpExtension,$newName);

                Utils::changeClass($newName,
                    [
                       $this->argument['controller']=>$this->argument['rename']
                    ]);

            }

            echo $this->classical($this->argument['controller'].' Controller has been changed as '.$this->argument['rename'].' Controller');

        }
    }

    /**
     * @return void
     */
    private function docUpdate()
    {
        $docPath = $this->directory['configuration'] .'/Doc.yaml';

        $doc = Utils::yaml($docPath);

        $data = [

            'index'=>[
                'http'=>'get',
                'params'=>[
                    'page'=>[
                        'required'=>false,
                        'type'=>'numeric'
                    ]
                ],
                'headers'=>[],
            ]
        ];

        $doc->set($data);
    }
}