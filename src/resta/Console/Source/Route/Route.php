<?php

namespace Resta\Console\Source\Route;

use Resta\Foundation\PathManager\StaticPathList;
use Resta\Router\Route as Router;
use Resta\Console\ConsoleOutputter;
use Resta\Console\ConsoleListAccessor;
use Resta\Support\ReflectionProcess;
use Resta\Support\Utils;

class Route extends ConsoleOutputter {

    use ConsoleListAccessor;

    /**
     * @var $type
     */
    public $type='route';

    /**
     * @var array
     */
    protected $runnableMethods = [
        'list'=>'lists all routes for project'
    ];

    /**
     * @var bool
     */
    protected $projectStatus = true;

    /**
     * @var $commandRule
     */
    public $commandRule=[];

    /**
     * @return bool
     */
    public function list(){

        echo $this->info('All Route Controller Lists :');

        $this->table->setHeaders(['no','endpoint','http','namespace','method','definition','middleware','event','doc','status']);

        $routes = Router::getRoutes();
        $routeData = isset($routes['data']) ? $routes['data'] : [];
        $routePattern = isset($routes['pattern']) ? $routes['pattern'] : [];

        $counter=0;

        foreach($routeData as $key=>$data){

            $endpoint = $data['endpoint'];
            $controllerNamespace = Utils::getNamespace($data['controller'].'/'.$data['namespace'].'/'.$data['class']);

            $methodDocument = app()['reflection']($controllerNamespace)->reflectionMethodParams($data['method'])->document;
            
            $methodDefinition = '';

            if(preg_match('@#define:(.*?)\n@is',$methodDocument,$definition)){
                if(isset($definition[1])){
                    $methodDefinition = rtrim($definition[1]);
                }
            }

            $endpointData = $endpoint.'/'.implode("/",$routePattern[$key]);

            if(isset($this->argument['filter'])){

                if(preg_match('@'.$this->argument['filter'].'@is',$endpointData)){

                    $this->table->addRow([
                        ++$counter,
                        $endpointData,
                        $data['http'],
                        $controllerNamespace,
                        $data['method'],
                        $methodDefinition,
                        '',
                        '',
                        '',
                        ''
                    ]);
                }
            }
            else{

                $this->table->addRow([
                    ++$counter,
                    $endpointData,
                    $data['http'],
                    $controllerNamespace,
                    $data['method'],
                    $methodDefinition,
                    '',
                    '',
                    '',
                    ''
                ]);
            }


        }


        echo $this->table->getTable();

    }
}