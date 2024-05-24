<?php
/**
* function that filter empty cells of any
* @author claudio966
* @access public
* @return an array 
*/

function db_connect() {
	require_once __DIR__ . "/../../var.php";
	require_once(CONF_MYSQL);
	require_once __DIR__ . "/../function_commun.php";

	$_SESSION["OCS"]["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);

	return $_SESSION["OCS"]["readServer"];
}

class ComponentsNotification 
{
	public $html_part_addition;
	public $html_part_remove;

	public $removed_data = array(
		'cpus' => [],
		'memories' => [],
		'monitors' => [],
		'storages' => [],
		'videos' => []
	);
	
	public $added_data = array(
		'cpus' => [],
		'memories' => [],
		'monitors' => [],
		'storages' => [],
		'videos' => []
	);

	public function added_json () {
    $hasElements = false;
    
    foreach ($this->added_data as $key => $value) {
      if (!empty($value)) {
        $hasElements = true;
        break;
      }
    }
    
    if ($hasElements) {
      return json_encode($this->added_data);
    } else {
        return null;
    }
  }

  public function removed_json () {
    $hasElements = false;
    
    foreach ($this->removed_data as $key => $value) {
      if (!empty($value)) {
        $hasElements = true;
        break;
      }
    }
    
    if ($hasElements) {
      return json_encode($this->removed_data);
    } else {
        return null;
    }
  }

	public function get_html_info_addition($list_hardware, $connection, $hard_component) {
		if ($this->html_part_addition == '') {
				// Import css style 
				$this->html_part_addition .= "
				 " .  file_get_contents(__DIR__ . '/html_css_part.html') . "
				<center><hr><div id='container'>
					<div id='cabecalho'>
					<div id='info-sistema'>
					<h3> Hardware Components have been Added </h3>
					</div>
					</div>
					</div><hr></center>";
		}
		$this->html_part_addition .= "
				<br>
				<table class='tabelaRelatorio' width='100%'>
				<caption> $hard_component <caption>
				<tr><thead>\n";

		$reference_array = array_key_first($list_hardware);
		foreach ($list_hardware[$reference_array] as $label => $value) {
			$this->html_part_addition .= "<th class='nota'>$label</th>\n";
		} 
		$this->html_part_addition .= "</tr></thead>\n<tbody><tr class='linhaPar linha'>";
	
		$id_asset = NULL; 
		foreach (array_keys($list_hardware) as $key) {
			foreach ($list_hardware["$key"] as $feature => $value) {
				if ($value == 'Unknown' or $value == '') { 
					$this->html_part_addition .= "<td class='nota' nowrap='nowrap'> Uninformed </td>\n";
					continue;
				}
				$this->html_part_addition .= "<td class='nota'>$value</td>\n";
		  } 
			$this->html_part_addition .= "</tr><tr class='linhaPar linha'>";
		}

		$this->html_part_addition .= "<td class='nota' nowrap='nowrap' bgcolor='#4169e1'>Total: " . count($list_hardware) . "</td></tr>";
		$this->html_part_addition .= "</table></tbody><br>";
	}
	
	public function get_html_info_removed($hardware_cache, $connection, $hard_component) {
		if ($this->html_part_remove == '') {
				// import css style
				$this->html_part_remove .= "
				" . file_get_contents(__DIR__ . '/html_css_part.html') ."
					<center><hr><div id='container'>
					<div id='cabecalho'>
					<div id='info-sistema'>
					<h3> Hardware Components have been Removed </h3>
					</div>
					</div>
					</div><hr></center>";
		}
		$this->html_part_remove .= "
				<br>
					<table class='tabelaRelatorio' width='100%'>
					<caption> $hard_component <caption>	
					<tr><thead>\n";
		$reference_array = array_key_first($hardware_cache);
		foreach ($hardware_cache[$reference_array] as $label => $value) {
			$this->html_part_remove .= "<th class='nota'>$label</th>\n";
		}
		$this->html_part_remove .= "</tr>\n</thead><tbody><tr class='linhaPar linha'>";

		$id_asset = NULL;
		foreach (array_keys($hardware_cache) as $key) {
			foreach ($hardware_cache["$key"] as $feature => $value) {
				if ($value == 'Unknown' or $value == '') {
					$this->html_part_remove .= "<td class='nota' nowrap='nowrap'> Uninformed </td>\n";
					continue;
				}
				$this->html_part_remove .= "<td class='nota' nowrap='nowrap'>$value</td>\n";
			}

			$this->html_part_remove .= "</tr><tr class='linhaPar linha'>";
		}
		$this->html_part_remove .= "<td class='nota' bgcolor='#4169e1' nowrap='nowrap'>Total: " . count($hardware_cache) . "</td></tr>";
		$this->html_part_remove .= "</table></tbody><br>";
			
	}
	/**
	* A method that collect information about the cpu of asset
	* @access public
	* @return void 
	*/
	public function get_cpus() {
		$connection = db_connect();
		$sql = "SELECT accountinfo.TAG, cpus.MANUFACTURER, cpus.TYPE, 'Added' as STATUS FROM cpus, accountinfo 
			WHERE accountinfo.TAG NOT IN (SELECT accountinfo.TAG FROM cpus_cache, accountinfo 
			WHERE accountinfo.HARDWARE_ID = cpus_cache.H_ID) and 
			accountinfo.HARDWARE_ID = cpus.HARDWARE_ID;";
		$result_query = mysqli_query($connection, $sql);
		
		$added_cpus = array();
		$asterix_amount = 0;
		while ($item_cpu = mysqli_fetch_array($result_query)) {
			if (array_key_exists($item_cpu['TAG'], $removed_cpus)) {
				$array_key = $item_cpu['TAG'] . str_repeat('*', ++$asterix_amount);
			} else {
				$array_key = $item_cpu['TAG'];
				$asterix_amount = 0;
			}

			$added_cpus[$array_key]['ASSET'] = $item_cpu['TAG'];
			$added_cpus[$array_key]['MANUFACTURER'] = $item_cpu['MANUFACTURER'];
			$added_cpus[$array_key]['TYPE'] = $item_cpu['TYPE'];
			$added_cpus[$array_key]['STATUS'] = $item_cpu['STATUS'];
		}
		$removed_cpus = array();
		$sql_cache = "SELECT id_assets_cache.TAG, cpus_cache.MANUFACTURER, cpus_cache.TYPE, 'Removed' as STATUS 
				FROM cpus_cache, id_assets_cache WHERE id_assets_cache.TAG NOT IN 
				(SELECT id_assets_cache.TAG FROM cpus, id_assets_cache 
				WHERE id_assets_cache.H_ID = cpus.HARDWARE_ID) and 
				id_assets_cache.H_ID = cpus_cache.H_ID;";
		$result_query_cache = mysqli_query($connection, $sql_cache);

		$asterix_amount = 0;
		while ($item_cpu = mysqli_fetch_array($result_query_cache)) {
			if (array_key_exists($item_cpu['TAG'], $removed_cpus)) {
				$array_key = $item_cpu['TAG'] . str_repeat('*', ++$asterix_amount);
			} else {
				$array_key = $item_cpu['TAG'];
				$asterix_amount = 0;
			}

			$removed_cpus[$array_key]['ASSET'] = $item_cpu['TAG'];
			$removed_cpus[$array_key]['MANUFACTURER'] = $item_cpu['MANUFACTURER'];
			$removed_cpus[$array_key]['TYPE'] = $item_cpu['TYPE'];
			$removed_cpus[$array_key]['STATUS'] = $item_cpu['STATUS'];
		}

		if ($added_cpus != NULL) {
			$this->added_data['cpus'] = $added_cpus;
			$this->get_html_info_addition($added_cpus, $connection, $hard_component = "Cpu(s)");
		}

		if ($removed_cpus != NULL) {
			$this->removed_data['cpus'] = $removed_cpus;
			$this->get_html_info_removed($removed_cpus, $connection, $hard_component = "Cpu(s)");
		}
		if ($added_cpus != NULL or $removed_cpus != NULL) {
			$sql = "TRUNCATE TABLE cpus_cache;";
			$sql .= "REPLACE INTO cpus_cache(ID, H_ID, TYPE, MANUFACTURER) SELECT ID, HARDWARE_ID, TYPE, MANUFACTURER FROM cpus;";
			mysqli_multi_query($connection, $sql);
		}
	}

	/**
	* A method that collect information about the memories of asset
	* @access public
	* @return void 
	*/
	public function get_memories() {
		$connection = db_connect();
		$sql = "SELECT accountinfo.TAG, concat(memories.CAPACITY, ' MB') as CAPACITY, memories.TYPE, 'Added' as STATUS 
			FROM memories, accountinfo 
			WHERE accountinfo.TAG NOT IN (SELECT accountinfo.TAG FROM memories_cache, accountinfo 
			WHERE accountinfo.HARDWARE_ID = memories_cache.H_ID) and 
			accountinfo.HARDWARE_ID = memories.HARDWARE_ID and capacity > 1024;";
		$result_query = mysqli_query($connection, $sql);
		
		$added_memories = array();
		$asterix_amount = 0;
		while ($item_memory = mysqli_fetch_array($result_query)) {
			if (array_key_exists($item_memory['TAG'], $added_memories)) {
				$array_key = $item_memory['TAG'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_memory['TAG'];
				$asterix_amount = 0;
			}
			$added_memories[$array_key]['ASSET'] = $item_memory['TAG'];
			$added_memories[$array_key]['TYPE'] = $item_memory['TYPE'];
			$added_memories[$array_key]['CAPACITY'] = $item_memory['CAPACITY'];
			$added_memories[$array_key]['STATUS'] = $item_memory['STATUS'];
		}

		$sql_cache = "SELECT id_assets_cache.TAG, concat(memories_cache.CAPACITY, ' MB') as CAPACITY, 
				memories_cache.TYPE, 'Removed' as STATUS FROM memories_cache, id_assets_cache 
				WHERE id_assets_cache.TAG NOT IN 
				(SELECT id_assets_cache.TAG FROM memories, id_assets_cache 
				WHERE id_assets_cache.H_ID = memories.HARDWARE_ID) and id_assets_cache.H_ID = memories_cache.H_ID 
				and CAPACITY > 1024;";
		$result_query_cache = mysqli_query($connection, $sql_cache);

		$removed_memories = array();
		$asterix_amount = 0;
		while ($item_memory = mysqli_fetch_array($result_query_cache)) {
			if (array_key_exists($item_memory['TAG'], $removed_memories)) {
				$array_key = $item_memory['TAG'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_memory['TAG'];
				$asterix_amount = 0;
			}
			$removed_memories[$array_key]['ASSET'] = $item_memory['TAG'];
			$removed_memories[$array_key]['TYPE'] = $item_memory['TYPE'];
			$removed_memories[$array_key]['CAPACITY (MB)'] = $item_memory['CAPACITY'];
			$removed_memories[$array_key]['STATUS'] = $item_memory['STATUS'];
		}

		if ($added_memories != NULL) {
			$this->added_data['memories'] = $added_memories;
			$this->get_html_info_addition($added_memories, $connection, $hard_component = "Memory(ies)");
		}

		if ($removed_memories != NULL) {
			$this->removed_data['memories'] = $removed_memories;
			$this->get_html_info_removed($removed_memories, $connection, $hard_component = "Memory(ies)");
		}
		
		if ($added_memories != NULL or $removed_memories != NULL) {
			$sql = "TRUNCATE TABLE memories_cache;";
			$sql .= "REPLACE INTO memories_cache(ID, H_ID, CAPACITY, TYPE) SELECT ID, HARDWARE_ID, CAPACITY, TYPE FROM memories;";
			mysqli_multi_query($connection, $sql);
		}
	}

	/**
	* A method that collect information about the monitors of asset
	* @access public
	* @return void 
	*/
	public function get_monitors() {
		$connection = db_connect();
		$sql = "SELECT accountinfo.TAG, monitors.MANUFACTURER, monitors.DESCRIPTION, 'Added' as Status 
				FROM monitors, accountinfo 
				WHERE accountinfo.TAG NOT IN (SELECT accountinfo.TAG FROM monitors_cache, accountinfo 
				WHERE accountinfo.HARDWARE_ID = monitors_cache.H_ID) and 
				accountinfo.HARDWARE_ID = monitors.HARDWARE_ID;";

		$result_query = mysqli_query($connection, $sql);
		
		$added_monitors = array();
		$asterix_amount = 0;
		while($item_monitor = mysqli_fetch_array($result_query)) {
			if (array_key_exists($item_monitor['TAG'], $added_monitors)) {
				$array_key = $item_monitor['TAG'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_monitor['TAG'];
				$asterix_amount = 0;
			}
			
			$added_monitors[$array_key]['MANUFACTURER'] = $item_monitor['MANUFACTURER'];		
			$added_monitors[$array_key]['DESCRIPTION'] = $item_monitor['DESCRIPTION'];		
			$added_monitors[$array_key]['ASSET'] = $item_monitor['HARDWARE_ID'];		
			$added_monitors[$array_key]['STATUS'] = $item_monitor['STATUS'];		
		}
		
		$sql_cache = "SELECT id_assets_cache.TAG, monitors_cache.MANUFACTURER, monitors_cache.DESCRIPTION, 
				'Removed' as STATUS FROM monitors_cache, id_assets_cache 
				WHERE id_assets_cache.TAG NOT IN 
				(SELECT id_assets_cache.TAG FROM monitors, id_assets_cache 
				WHERE id_assets_cache.H_ID = monitors.HARDWARE_ID) and 
				id_assets_cache.H_ID = monitors_cache.H_ID;";
		$result_query_cache = mysqli_query($connection, $sql_cache);

		$removed_monitors = array();
		$asterix_amount = 0;
		while ($item_monitor = mysqli_fetch_array($result_query_cache)) {
			if (array_key_exists($item_monitor['TAG'], $removed_monitors)) {
				$array_key = $item_monitor['TAG'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_monitor['TAG'];
				$asterix_amount = 0;
			}
			
			$removed_monitors[$array_key]['MANUFACTURER'] = $item_monitor['MANUFACTURER'];
			$removed_monitors[$array_key]['DESCRIPTION'] = $item_monitor['DESCRIPTION'];		
			$removed_monitors[$array_key]['ASSET'] = $item_monitor['TAG'];		
		}
		

		if ($added_monitors != NULL) {
			$this->added_data['monitors'] = $added_monitors;
			$this->get_html_info_addition($added_monitors, $connection, $hard_component = "Monitor(s)");
		}
		
		if ($removed_monitors != NULL) {
			$this->removed_data['monitors'] = $removed_monitors;
			$this->get_html_info_removed($removed_monitors, $connection, $hard_component = "Monitor(s)");
		}
		

		if ($added_monitors != NULL or $removed_monitors != NULL) {
			$sql = "TRUNCATE TABLE monitors_cache;";
			$sql .= "REPLACE INTO monitors_cache(ID, H_ID, MANUFACTURER, DESCRIPTION) SELECT ID, HARDWARE_ID, MANUFACTURER, DESCRIPTION FROM monitors;";
			mysqli_multi_query($connection, $sql);
		}
	}

	/**
	* A method that collect information about the storages(like HD, SSD, ...) of asset
	* @access public
	* @return void 
	*/
	public function get_storages() {
		$connection = db_connect();
		$sql = "SELECT accountinfo.TAG, storages.MANUFACTURER, storages.MODEL, concat(storages.DISKSIZE, ' MB') as DISKSIZE, 
			'Added' as STATUS FROM storages, accountinfo 
			WHERE accountinfo.TAG NOT IN (SELECT accountinfo.TAG 
			FROM storages_cache, accountinfo WHERE accountinfo.HARDWARE_ID = storages_cache.H_ID) 
			and accountinfo.HARDWARE_ID = storages.HARDWARE_ID and DISKSIZE > 32000;";
		$result_query = mysqli_query($connection, $sql);
		
		$added_storages = array();
		$asterix_amount = 0;
		while ($item_storages = mysqli_fetch_array($result_query)) {
			if (array_key_exists($item_storages['TAG'], $added_storages)) {
				$array_key = $item_storages['TAG'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_storages['TAG'];
				$asterix_amount = 0;
			}

			$added_storages[$array_key]['ASSET'] = $item_storages['TAG'];
			$added_storages[$array_key]['MANUFACTURER'] = $item_storages['MANUFACTURER'];
			$added_storages[$array_key]['DISKSIZE (MB)'] = $item_storages['DISKSIZE'];
			$added_storages[$array_key]['MODEL'] = $item_storages['MODEL'];
			$added_storages[$array_key]['STATUS'] = $item_storages['STATUS'];
		}
		
		$sql_cache = "SELECT id_assets_cache.TAG, storages_cache.MANUFACTURER, concat(storages_cache.DISKSIZE, ' MB') as DISKSIZE,
 				storages_cache.MODEL, 'Removed' as STATUS FROM storages_cache, id_assets_cache 
				WHERE id_assets_cache.TAG NOT IN (SELECT id_assets_cache.TAG 
				FROM storages, id_assets_cache WHERE id_assets_cache.H_ID = storages.HARDWARE_ID) and 
				id_assets_cache.H_ID = storages_cache.H_ID and DISKSIZE > 32000;";
		$result_query_cache = mysqli_query($connection, $sql_cache);
		
		$removed_storages = array();
		$asterix_amount = 0;
		while ($item_storages = mysqli_fetch_array($result_query_cache)) {
			if (array_key_exists($item_storages['TAG'], $removed_storages)) {
				$array_key = $item_storages['TAG'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_storages['TAG'];
				$asterix_amount = 0;
			}

			$removed_storages[$array_key]['MANUFACTURER'] = $item_storages['MANUFACTURER'];
			$removed_storages[$array_key]['DISKSIZE (MB)'] = $item_storages['DISKSIZE'];
			$removed_storages[$array_key]['MODEL'] = $item_storages['MODEL'];
			$removed_storages[$array_key]['ASSET'] = $item_storages['TAG'];
		}
		
		if ($added_storages != NULL) {
			$this->added_data['storages'] = $added_storages;
			$this->get_html_info_addition($added_storages, $connection, $hard_component = "Storage(s)");
		}
		
		if ($removed_storages != NULL) {
			$this->removed_data['storages'] = $removed_storages;
			$this->get_html_info_removed($removed_storages, $connection, $hard_component = "Storage(s)");
		}

		if ($added_storages != NULL or $removed_storages != NULL) {
			$sql = "TRUNCATE TABLE storages_cache;";
			$sql .= "REPLACE INTO storages_cache(ID, H_ID, MANUFACTURER, DISKSIZE, MODEL) SELECT ID, HARDWARE_ID, MANUFACTURER, DISKSIZE, MODEL FROM storages;";
			mysqli_multi_query($connection, $sql);
		}
	}

	/**
	* A method that collect information about the board videos of asset
	* @access public
	* @return void 
	*/
	public function get_videos() {
		$connection = db_connect();
		$sql = "SELECT accountinfo.TAG, videos.NAME, videos.MEMORY, 'Added' as STATUS 
			FROM videos, accountinfo WHERE accountinfo.TAG NOT IN (SELECT accountinfo.TAG FROM videos_cache, accountinfo 
			WHERE accountinfo.HARDWARE_ID = videos_cache.H_ID) 
			and accountinfo.HARDWARE_ID = videos.HARDWARE_ID;";
		$result_query = mysqli_query($connection, $sql);

		$added_videos = array();
		$asterix_amount = 0;
		while($item_videos = mysqli_fetch_array($result_query)) {
			if (array_key_exists($item_videos['TAG'], $added_videos)) {
				$array_key = $item_videos['TAG'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_videos['TAG'];
				$asterix_amount = 0;
			}
			$added_videos[$array_key]['ASSET'] = $item_videos['TAG'];		
			$added_videos[$array_key]['NAME'] = $item_videos['NAME'];		
			$added_videos[$array_key]['MEMORY'] = $item_videos['MEMORY'];		
			$added_videos[$array_key]['STATUS'] = $item_videos['STATUS'];		
		}

		$sql_cache = "SELECT id_assets_cache.TAG, videos_cache.NAME, concat(videos_cache.MEMORY, ' MB') as MEMORY, 
				'Removed' as STATUS FROM videos_cache, id_assets_cache WHERE id_assets_cache.TAG NOT IN 
				(SELECT id_assets_cache.TAG FROM videos, id_assets_cache 
				WHERE id_assets_cache.H_ID = videos.HARDWARE_ID) and 
				id_assets_cache.H_ID = videos_cache.H_ID;";
		$result_query_cache = mysqli_query($connection, $sql_cache);

		$removed_videos = array();
		$asterix_amount = 0;
		while($item_videos = mysqli_fetch_array($result_query_cache)) {
			if (array_key_exists($item_videos['TAG'], $removed_videos)) {
				$array_key = $item_videos['TAG'] . str_repeat("*", ++$asterix_amount);
			} else {
				$array_key = $item_videos['TAG'];
				$asterix_amount = 0;
			}	
			
			$removed_videos[$array_key]['NAME'] = $item_videos['NAME'];		
			$removed_videos[$array_key]['MEMORY'] = $item_videos['MEMORY'];		
			$removed_videos[$array_key]['ASSET'] = $item_videos['TAG'];		
		}
		
		
		if ($added_videos != NULL) {
			$this->added_data['videos'] = $added_videos;
			$this->get_html_info_addition($added_videos, $connection, $hard_component = "C. Video(s)");
		}
		
		if ($removed_videos != NULL) {
			$this->removed_data['videos'] = $removed_videos;
			$this->get_html_info_removed($removed_videos, $connection, $hard_component = "C. Video(s)");
		}

		
		if ($added_videos != NULL or $removed_videos != NULL) {
			$sql = "TRUNCATE TABLE videos_cache;";
			$sql .= "REPLACE INTO videos_cache(ID, H_ID, NAME, MEMORY) SELECT ID, HARDWARE_ID, NAME, MEMORY FROM videos;";
			mysqli_multi_query($connection, $sql);
		}
		
	}
	/**
	* A function that update the fields id and tag of cache table, whenever a asset is removed or added
	* @access public
	* @return void 
	*/
	public function update_id_assets() {
		$connection = db_connect();
		$sql = "TRUNCATE TABLE id_assets_cache;";
		$sql .= "REPLACE INTO id_assets_cache(H_ID, TAG) SELECT HARDWARE_ID, TAG FROM accountinfo;";
		mysqli_multi_query($connection, $sql);
	}
}
