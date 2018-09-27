<?php

namespace TheWebmen\PickerField\Controllers;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\ORM\SS_List;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use Symbiote\GridFieldExtensions\GridFieldTitleHeader;

class PickerField extends GridField {
	protected $isHaveOne = false;

	/**
	 * Usage [e.g. in getCMSFields]
	 *    $field = new PickerField('Authors', 'Selected Authors', $this->Authors(), 'Select Author(s)');
	 *
	 * @param string $name              - Name of field (typically the relationship method)
	 * @param string $title             - GridField Title
	 * @param SS_List $dataList         - Result of the relationship component method (E.g. $this->Authors())
	 * @param string $linkExistingTitle - AddExisting Button Title
	 * @param string $sortField         - Field to sort on. Be sure it exists in the $many_many_extraFields static
	 */
	public function __construct($name, $title = null, SS_List $dataList = null, $linkExistingTitle = null, $sortField = null) {
		$config = GridfieldConfig::create()->addComponents(
			new GridFieldButtonRow('before'),
			new GridFieldToolbarHeader(),
			new GridFieldDataColumns(),
			new GridFieldTitleHeader(),
			new GridFieldPaginator(),
			new PickerFieldAddExistingSearchButton('buttons-before-left'),
			new PickerFieldDeleteAction()
		);

		if($sortField)
		{
			$config->addComponent(new GridFieldOrderableRows($sortField));
		}

		if(!$linkExistingTitle)
		{
		    $dataClassName = array_values(array_slice(explode("\\", $dataList->dataClass()), -1))[0];
			$linkExistingTitle = ($this->isHaveOne()) ?
				'Select a ' . $dataClassName :		// singular [has_one]
				'Select ' . $dataClassName . '(s)';	// plural [has_many, many_many]
		}

		$config->getComponentByType(PickerFieldAddExistingSearchButton::class)->setTitle($linkExistingTitle);

		return parent::__construct($name, $title, $dataList, $config);
	}

	public function isHaveOne(){
		return $this->isHaveOne;
	}

	public function setSearchFilters($filters) {
		$this->config->getComponentByType(PickerFieldAddExistingSearchButton::class)
			->setSearchFilters($filters);

		return $this;
	}

	public function setSearchExcludes($excludes) {
		$this->config->getComponentByType(PickerFieldAddExistingSearchButton::class)
			->setSearchExcludes($excludes);

		return $this;
	}

	/**
	 * @param SS_List $list List to search on
	 * @return $this
	 */
	public function setSearchList(SS_List $list) {
		$this->config->getComponentByType(PickerFieldAddExistingSearchButton::class)
			->setSearchList($list);

		return $this;
	}

	public function getSearchFilters() {
		return $this->config->getComponentByType(PickerFieldAddExistingSearchButton::class)
			->getSearchFilters();
	}

	public function getSearchExcludes() {
		return $this->config->getComponentByType(PickerFieldAddExistingSearchButton::class)
			->getSearchExcludes();
	}

	/**
	 * @return SS_List
	 */
	public function getSearchLIst() {
		return $this->config->getComponentByType(PickerFieldAddExistingSearchButton::class)
			->getSearchList();
	}

	public function enableCreate($button_title = null) {
	    $this->addDetailForm();

	    $button = new GridFieldAddNewButton('buttons-before-left');
	    if($button_title) $button->setButtonName($button_title);

	    $this->config->addComponent($button);

	    return $this;
	}

	public function enableEdit() {
	    $this->addDetailForm();

	    $this->config->addComponent(new GridFieldEditButton());

	    return $this;
	}


	public function setSelectTitle($title) {
	    $this->config->getComponentByType(PickerFieldAddExistingSearchButton::class)->setTitle($title);

	    return $this;
	}

	private function addDetailForm(){

	    if($this->config->getComponentByType(GridFieldDetailForm::class))
	        return;

	    $form = new GridFieldDetailForm();
	    $form->setItemRequestClass(PickerFieldEditHandler::class);


	    return $this->config->addComponent($form);
	}
}
