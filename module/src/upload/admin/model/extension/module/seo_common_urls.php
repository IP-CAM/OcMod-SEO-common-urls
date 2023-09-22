<?php

/*
<insertfile>_inc/summary.txt</insertfile>
*/

class ModelExtensionModuleSeoCommonUrls extends Model {
	public function addCommonUrlKeywords(array $seo_keywords) {
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();

		$query = $this->db->query('SELECT `store_id` FROM `' . DB_PREFIX . 'store` ORDER BY `store_id`');

		$stores = $query->rows;
		array_unshift($stores, array('store_id' => 0));

		foreach ($languages as $language) {
			$language_id = $language['language_id'];

			foreach ($seo_keywords as $query => $keyword) {
				$sql = 'SELECT * FROM `' . DB_PREFIX . 'seo_url` ' .
					'WHERE (' .
						'`query` = ' . "'" . $this->db->escape($query) . "'" .
						' AND `language_id` = ' . (int)$language_id .
					') LIMIT 1';

				if (!$this->db->query($sql)->num_rows) {
					foreach ($stores as $store) {
						$sql =
							'INSERT INTO `' . DB_PREFIX . 'seo_url` (query, keyword, language_id, store_id) ' .
							'VALUES (' .
								"'" . $this->db->escape($query) . "', " .
								"'" . $this->db->escape($keyword) . "', " .
								(int)$language_id . ', ' .
								(int)$store['store_id'] .
							')';

						$this->db->query($sql);
					}
				} else {
					// force update existing entries
					if (1) {
						$sql =
							'UPDATE `' . DB_PREFIX . 'seo_url` ' .
							'SET ' .
								'`keyword` = ' . "'" . $this->db->escape($keyword) . "' " .
							'WHERE ' .
								'`query` =  ' . "'" . $this->db->escape($query) . "'";

						$this->db->query($sql);
					}
				}
			}
		}

		return true;
	}

	public function deleteCommonUrlKeywords(array $seo_keywords) {
		foreach ($seo_keywords as $query => $keyword) {
			$sql =
				'DELETE FROM `' . DB_PREFIX . 'seo_url` ' .
				'WHERE ' .
					'`query` = ' . "'" . $this->db->escape($query) . "'";

			$this->db->query($sql);
		}

		return true;
	}
}
