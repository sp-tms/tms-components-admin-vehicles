<?php

namespace Apps\Tms\Components\Vehicles;

use Apps\Tms\Packages\Adminltetags\Traits\DynamicTable;
use Apps\Tms\Packages\Companies\Companies;
use Apps\Tms\Packages\Tools\Uom\ToolsUom;
use Apps\Tms\Packages\Vehicles\Vehicles;
use System\Base\BaseComponent;

class VehiclesComponent extends BaseComponent
{
    use DynamicTable;

    protected $vehiclesPackage;

    protected $companiesPackage;

    protected $toolsUomPackage;

    public function initialize()
    {
        $this->vehiclesPackage = $this->usePackage(Vehicles::class);

        $this->companiesPackage = $this->usePackage(Companies::class);

        $this->toolsUomPackage = $this->usePackage(ToolsUom::class);
    }

    /**
     * @acl(name=view)
     */
    public function viewAction()
    {
        $this->view->uoms = [];

        if (isset($this->getData()['id'])) {
            $this->useStorage('private');

            $organisations = $this->companiesPackage->getCompaniesByBusinessType();
            if ($organisations && count($organisations) > 0) {
                foreach ($organisations as &$organisation) {
                    $organisation['name'] = $organisation['name'] . ' (' . $organisation['pan'] . ')';
                }
            }
            $this->view->organisations = $organisations;

            $vendors = $this->companiesPackage->getCompaniesByBusinessType('vendors');
            if ($vendors && count($vendors) > 0) {
                foreach ($vendors as &$vendor) {
                    $vendor['name'] = $vendor['name'];
                }
            }
            $this->view->vendors = $vendors;

            //Available Vehicle Status
            $this->view->vehicleStatuses = $this->vehiclesPackage->getVehicleAvailableStatus();

            //Available UoMS
            $this->view->uoms = $this->toolsUomPackage->getAll()->toolsuom;

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
     * @notification(name=add)
     */
    public function addAction()
    {
        $this->requestIsPost();

        $this->vehiclesPackage->addVehicle($this->postData());

        $this->addResponse(
            $this->vehiclesPackage->packagesData->responseMessage,
            $this->vehiclesPackage->packagesData->responseCode
        );

        if ($this->vehiclesPackage->packagesData->responseCode === 0) {
            $this->addToNotification('add', 'Added new vehicle ' . $this->vehiclesPackage->packagesData->last['name'], null, $this->vehiclesPackage->packagesData->last);
        }
    }

    /**
     * @acl(name=update)
     * @notification(name=update)
     */
    public function updateAction()
    {
        $this->requestIsPost();

        $this->vehiclesPackage->useMutex(true);

        $this->vehiclesPackage->updateVehicle($this->postData());

        $this->addResponse(
            $this->vehiclesPackage->packagesData->responseMessage,
            $this->vehiclesPackage->packagesData->responseCode
        );

        if ($this->vehiclesPackage->packagesData->responseCode === 0) {
            $this->addToNotification('update', 'Updated vehicle ' . $this->vehiclesPackage->packagesData->last['name'], null, $this->vehiclesPackage->packagesData->last);
        }
    }

    /**
     * @acl(name=remove)
     * @notification(name=remove)
     */
    public function removeAction()
    {
        $this->requestIsPost();

        $this->vehiclesPackage->removeVehicle($this->postData());

        $this->addResponse(
            $this->vehiclesPackage->packagesData->responseMessage,
            $this->vehiclesPackage->packagesData->responseCode
        );

        if ($this->vehiclesPackage->packagesData->responseCode === 0) {
            $this->addToNotification('remove', 'Archived vehicle ' . $this->vehiclesPackage->packagesData->last['name'], null, $this->vehiclesPackage->packagesData->last);
        }
    }

    public function updateDocumentAction()
    {
        $this->requestIsPost();

        $this->vehiclesPackage->updateDocument($this->postData());

        $this->addResponse(
            $this->vehiclesPackage->packagesData->responseMessage,
            $this->vehiclesPackage->packagesData->responseCode,
            $this->vehiclesPackage->packagesData->responseData ?? []
        );
    }
}