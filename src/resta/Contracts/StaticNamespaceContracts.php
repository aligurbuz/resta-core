<?php

namespace Resta\Contracts;

interface StaticNamespaceContracts {

    /**
     * @param null $endpoint
     * @param array $bind
     * @return mixed
     */
    public function call($endpoint=null,$bind=array());

    /**
     * @param null $command
     * @param array $argument
     * @return mixed
     */
    public function command($command=null,$argument=array());

    /**
     * @return mixed
     */
    public function config();

    /**
     * @return mixed
     */
    public function kernel();

    /**
     * @return mixed
     */
    public function logger();

    /**
     * @return mixed
     */
    public function middleware();

    /**
     * @return mixed
     */
    public function migration();

    /**
     * @return mixed
     */
    public function model();

    /**
     * @return mixed
     */
    public function optionalException();

    /**
     * @return mixed
     */
    public function optionalJob();

    /**
     * @return mixed
     */
    public function optionalRepository();

    /**
     * @return mixed
     */
    public function optionalSource();

    /**
     * @return mixed
     */
    public function optionalWebservice();

    /**
     * @return mixed
     */
    public function serviceContainer();

    /**
     * @return mixed
     */
    public function version();
}