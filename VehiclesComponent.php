<?php

namespace Apps\Tms\Components\Vehicles;

use Apps\Tms\Packages\Adminltetags\Traits\DynamicTable;
use Apps\Tms\Packages\Vehicles\Vehicles;
use System\Base\BaseComponent;

class VehiclesComponent extends BaseComponent
{
    use DynamicTable;

    protected $vehiclesPackage;

    public function initialize()
    {
        $this->vehiclesPackage = $this->usePackage(Vehicles::class);
    }

    /**
     * @acl(name=view)
     */
    public function viewAction()
    {
        if (isset($this->getData()['id'])) {
            if ($this->getData()['id'] != 0) {
                $vehicle = $this->vehiclesPackage->getVehicle((int) $this->getData()['id']);

                if (!$vehicle) {
                    return $this->throwIdNotFound();
                }

                $this->view->vehicle = $vehicle;
            }

            $this->view->pick('vehicles/view');

            return;
        }

        $controlActions =
            [
                'actionsToEnable'       =>
                [
                    'edit'      => 'vehicles'
                ]
            ];

        $conditions = [];
        $conditions['order'] = 'name asc';

        $replaceColumns =
            function ($dataArr) {
                if ($dataArr && is_array($dataArr) && count($dataArr) > 0) {
                    //
                }

                return $dataArr;
            };

        $this->generateDTContent(
            $this->vehiclesPackage,
            'vehicles/view',
            $conditions,
            ['registration_no'],
            true,
            ['registration_no'],
            $controlActions,
            ['registration_no' => 'Registration #'],
            $replaceColumns,
            'registration_no'
        );

        $this->view->pick('vehicles/list');
    }

    /**
     * @acl(name=add)
     */
    public function addAction()
    {
        $this->requestIsPost();

        $this->vehiclesPackage->addCompany($this->postData());

        $this->addResponse(
            $this->vehiclesPackage->packagesData->responseMessage,
            $this->vehiclesPackage->packagesData->responseCode
        );
    }

    /**
     * @acl(name=update)
     */
    public function updateAction()
    {
        $this->requestIsPost();

        $this->vehiclesPackage->updateCompany($this->postData());

        $this->addResponse(
            $this->vehiclesPackage->packagesData->responseMessage,
            $this->vehiclesPackage->packagesData->responseCode
        );
    }

    /**
     * @acl(name=remove)
     */
    public function removeAction()
    {
        $this->requestIsPost();

        $this->vehiclesPackage->removeCompany($this->postData());

        $this->addResponse(
            $this->vehiclesPackage->packagesData->responseMessage,
            $this->vehiclesPackage->packagesData->responseCode
        );
    }
}