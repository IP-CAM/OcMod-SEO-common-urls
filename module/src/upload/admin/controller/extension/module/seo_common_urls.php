<?php

/*
<insertfile>_inc/summary.txt</insertfile>
*/

class ControllerExtensionModuleSeoCommonUrls extends Controller {
	private $mname = 'seo_common_urls';
	private $mtype = 'module';
	private $mroute;
	private $mmodel;
	private $module;

	private $common_urls = array(
		'checkout/cart'          => 'cart',
		'checkout/checkout'      => 'checkout',
		'product/manufacturer'   => 'brand',
		'product/special'        => 'special',
		'product/compare'        => 'compare',
		'product/search'         => 'search',
		'information/contact'    => 'contact',
		'information/sitemap'    => 'sitemap',
		'account/account'        => 'account',
		'account/address'        => 'account/address',
		'account/address/edit'   => 'account/address/edit',
		'account/address/add'    => 'account/address/add',
		'account/address/delete' => 'account/address/delete',
		'account/download'       => 'account/download',
		'account/edit'           => 'account/edit',
		'account/forgotten'      => 'account/forgotten',
		'account/login'          => 'account/login',
		'account/logout'         => 'account/logout',
		'account/newsletter'     => 'account/newsletter',
		'account/order'          => 'account/order',
		'account/order/info'     => 'account/order/info',
		'account/password'       => 'account/password',
		'account/recurring'      => 'account/recurring',
		'account/return'         => 'account/return',
		'account/return/add'     => 'account/return/add',
		'account/register'       => 'account/register',
		'account/reward'         => 'account/reward',
		'account/transaction'    => 'account/transaction',
		'account/voucher'        => 'account/voucher',
		'account/wishlist'       => 'account/wishlist',
		'affiliate/account'      => 'affiliate',
		'affiliate/edit'         => 'affiliate/edit',
		'affiliate/forgotten'    => 'affiliate/forgotten',
		'affiliate/login'        => 'affiliate/login',
		'affiliate/logout'       => 'affiliate/logout',
		'affiliate/password'     => 'affiliate/password',
		'affiliate/payment'      => 'affiliate/payment',
		'affiliate/register'     => 'affiliate/register',
		'affiliate/tracking'     => 'affiliate/tracking',
		'affiliate/transaction'  => 'affiliate/transaction',
	);

	public function __construct($params) {
		parent::__construct($params);

		$this->checkVersion();

		$this->mroute = 'extension/' . $this->mtype . '/' . $this->mname;
		$this->mmodel = 'model_' . str_replace('/', '_', $this->mroute);
		$this->module = $this->mtype . '_' . $this->mname;

		$this->load->model($this->mroute);
	}

	public function index() {
		$this->load->language($this->mroute);

		$this->document->setTitle($this->language->get('heading_title'));

		$token = 'user_token=' . $this->session->data['user_token'];
		$extension_route = 'marketplace/extension';

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $token, true),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link($extension_route, $token . '&type=' . $this->mtype, true),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->mroute, $token, true),
		);

		$data['cancel'] = $this->url->link($extension_route, $token . '&type=' . $this->mtype, true);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->mroute, $data));
	}

	public function install() {
		$this->uninstall();

		$this->load->model('user/user_group');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', $this->mroute);
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', $this->mroute);

		$event_route = 'setting/event';
		$event_model = 'model_setting_event';

		$this->load->model($event_route);

		$event = $this->mname . '_admin';

		$trigger = 'admin/view/design/layout_form/before';
		$action = $this->mroute . '/beforeViewDesignLayoutForm';
		$this->{$event_model}->addEvent($event, $trigger, $action);

		$this->config->set($this->module . '_status', '1');

		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting($this->module, array(
			$this->module . '_status' => 1,
			$this->module => array(
				'common_urls' => $this->common_urls
			)
		));

		$this->{$this->mmodel}->addCommonUrlKeywords($this->common_urls);
	}

	public function uninstall() {
		$event_route = 'setting/event';
		$event_model = 'model_setting_event';
		$delete_method = 'deleteEventByCode';

		$this->load->model($event_route);

		$events = array(
			$this->mname . '_admin',
		);

		// Delete events
		foreach ($events as $event) {
			$this->{$event_model}->{$delete_method}($event);
		}

		$this->{$this->mmodel}->deleteCommonUrlKeywords($this->common_urls);

		$this->load->model('user/user_group');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', $this->mroute);
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', $this->mroute);
	}

	// https://forum.opencart.com/viewtopic.php?p=799279#p799279
	// admin/view/design/layout_form/before
	public function beforeViewDesignLayoutForm(&$route, &$data) {
		foreach ($data['extensions'] as $key => $extension) {
			if ($extension['code'] == $this->mname) {
				unset($data['extensions'][$key]);
			}
		}

		return null;
	}

	private function checkVersion() {
		$token = 'user_token=' . $this->session->data['user_token'];
		$extension_route = 'marketplace/extension';

		if (strcmp(VERSION, '3.0.0.0') < 0 || strcmp(VERSION, '4.0.0.0') >= 0) {
			$this->response->redirect($this->url->link($extension_route, $token . '&type=' . $this->mtype, true));

			exit();
		}
	}
}
