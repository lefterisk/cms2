<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Api\Controller;

use Administration\Helper\ListingHandler;
use Administration\Helper\ModelHandler;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Stdlib\Parameters;

class ApiController extends AbstractRestfulJsonController
{
    protected $errors = array();

    public function testAction()
    {
        $request = new Request();
        $request->getHeaders()->addHeaders(array(
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'
        ));
//        var_dump($this->url()->fromRoute('api',array(),array('force_canonical' => true)));
        $request->setUri($this->url()->fromRoute('api',array(),array('force_canonical' => true)));
        $request->setMethod('GET');
        $request->setQuery(new Parameters(array('model' => 'example','id'=>'1')));

        $client = new Client();
        $response = $client->dispatch($request);
        $data = json_decode($response->getBody(), true);

        return new JsonModel(array(
            'testResponse'=> $data
        ));
    }

    public function getList()
    {
        $requested_model  = $this->params()->fromQuery('model');
        $model            = new ModelHandler($requested_model, $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        if (!$model->isInitialised()) {
            $this->errors = array_merge($this->errors, $model->getErrors());
            return new JsonModel(array(
                'errors' => $this->errors
            ));
        }

        $parent = ($this->params()->fromQuery('parent')) ? $this->params()->fromQuery('parent') : 0;

        if ((int)$parent != 0 ) {
            try {
                $item = $model->getItemById($parent);
            } catch (\Exception $ex) {
                $this->errors = array_merge($this->errors, $model->getErrors());
                return new JsonModel(array(
                    'errors' => $this->errors
                ));
            }
        }

        $listingHandler     = new ListingHandler($model, $this->getServiceLocator()->get('SiteLanguages'));

        return new JsonModel(
            array('items' => $listingHandler->getListing($parent))
        );
    }

    public function get($id)
    {
        $requested_model = $this->params()->fromQuery('model');

        $model = new ModelHandler($requested_model, $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        if (!$model->isInitialised()) {
            $this->errors = array_merge($this->errors, $model->getErrors());
            return new JsonModel(array(
                'errors' => $this->errors
            ));
        }

        try {
            $item = $model->getItemById($id);
        } catch (\Exception $ex) {
            $this->errors = array_merge($this->errors, $model->getErrors());
            return new JsonModel(array(
                'errors' => $this->errors
            ));
        }

        return new JsonModel(
            array('item' => $item)
        );
    }

    public function create($data)
    {   // Action used for POST requests
        return new JsonModel(array('data' => array('id'=> 3, 'name' => 'New Album', 'band' => 'New Band')));
    }

    public function update($id, $data)
    {   // Action used for PUT requests
        return new JsonModel(array('data' => array('id'=> 3, 'name' => 'Updated Album', 'band' => 'Updated Band')));
    }

    public function delete($id)
    {   // Action used for DELETE requests
        return new JsonModel(array('data' => 'album id 3 deleted'));
    }
}
