<?php
/**
Plugin Name: MyCRM Importer
Description: MyCRM Importer
Version: 1.0.0
Author: Indeed Software
Author URI: http://indeedsoft.com/
*/

$IDS_Amenities = array(
	"BA" => "Balcony",
	"BP" => "Basement parking",
	"BB" => "BBQ area",
	"AN" => "Cable-ready",
	"BW" => "Built in wardrobes",
	"CA" => "Carpets",
	"AC" => "Central air conditioning",
	"CP" => "Covered parking",
	"DR" => "Drivers room",
	"FF" => "Fully fitted kitchen",
	"GZ" => "Gazebo",
	"PY" => "Private Gym",
	"PJ" => "Jacuzzi",
	"BK" => "Kitchen Appliances",
	"MR" => "Maids Room",
	"MB" => "Marble floors",
	"HF" => "On high floor",
	"LF" => "On low floor",
	"MF" => "On mid floor",
	"PA" => "Pets allowed",
	"GA" => "Private garage",
	"PG" => "Garden",
	"PP" => "Swimming pool",
	"SA" => "Sauna",
	"SP" => "Shared swimming pool",
	"WF" => "Wood flooring",
	"SR" => "Steam room",
	"ST" => "Study",
	"UI" => "Upgraded interior",
	"GR" => "Garden view",
	"VW" => "Sea/Water view",
	"SE" => "Security",
	"MT" => "Maintenance",
	"IC" => "Within a Compound",
	"IS" => "Indoor swimming pool",
	"SF" => "Separate entrance for females",
	"BT" => "Basement",
	"SG" => "Storage room",
	"CV" => "Community view",
	"GV" => "Golf view",
	"CW" => "City view",
	"NO" => "North orientation",
	"SO" => "South orientation",
	"EO" => "East orientation",
	"WO" => "West orientation",
	"NS" => "Near school",
	"HO" => "Near hospital",
	"TR" => "Terrace",
	"NM" => "Near mosque",
	"SM" => "Near supermarket",
	"ML" => "Near mall",
	"PT" => "Near public transportation",
	"MO" => "Near metro",
	"VT" => "Near veterinary",
	"BC" => "Beach access",
	"PK" => "Public parks",
	"RT" => "Near restaurants",
	"NG" => "Near Golf",
	"AP" => "Near airport",
	"CS" => "Concierge Service",
	"SS" => "Spa",
	"SY" => "Shared Gym",
	"MS" => "Maid Service",
	"WC" => "Walk-in Closet",
	"HT" => "Heating",
	"GF" => "Ground floor",
	"SV" => "Server room",
	"DN" => "Pantry",
	"RA" => "Reception area",
	"VP" => "Visitors parking",
	"OP" => "Office partitions",
	"SH" => "Core and Shell",
	"CD" => "Children daycare",
	"CL" => "Cleaning services",
	"NH" => "Near Hotel",
	"CR" => "conference room",
);

if (!defined('WPINC'))
	die;

function UINT($value) {
	return intval(preg_replace("/[^0-9]/", "", $value));
}

class IDS_MycrmImporter
{
	public function install() {
	    global $wpdb;
	    if (!$wpdb->get_col("SHOW TABLES LIKE 'wp_mc_images'")) {
	    	$wpdb->query("
					CREATE TABLE `wp_mc_images` (
					  `id` int(11) NOT NULL,
					  `post_id` int(11) NOT NULL,
					  `url` text NOT NULL,
					  `saved` int(11) NOT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	    		");

	    	$wpdb->query("
					ALTER TABLE `wp_mc_images`
					  ADD PRIMARY KEY (`id`),
					  ADD KEY `post_id` (`post_id`);
	    		");

	    	$wpdb->query("
					ALTER TABLE `wp_mc_images`
					  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
	    		");
	    }
	}

	public function run()
	{
		add_action('admin_menu', array(&$this, 'adminMenu'));
		add_action('admin_init', array(&$this, 'initSettings'));

		add_action('wp_ajax_pfiAjaxImport', array(&$this, 'ajaxImport'));
		add_action('wp_ajax_pfiAjaxImportImages', array(&$this, 'ajaxImportImages'));

		add_action('wp_ajax_nopriv_pfiAjaxImport', array(&$this, 'ajaxImport'));
		add_action('wp_ajax_nopriv_pfiAjaxImportImages', array(&$this, 'ajaxImportImages'));
	}

	public function adminMenu()
	{
		add_options_page('Propery Finder', 'Propery Finder', 'administrator', 'propery-finder', array(&$this, 'displayOptionsPage'));
	}

	public function displayOptionsPage()
	{
		require('admin-options.php');
	}

	public function initSettings()
	{
	    register_setting('pfi_settings_group', 'pfi_feed_link');
	    register_setting('pfi_settings_group', 'pfi_last_update');
	}

	private function requestImageUpdate() {
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, "https://privilegeproperty.ae/wp-admin/admin-ajax.php?action=pfiAjaxImportImages"); 
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);

		curl_exec($curl);
		curl_close($curl);
	}

	public function ajaxImportImages()
	{
		global $wpdb;

		$imageRow = $wpdb->get_row("SELECT * FROM `wp_mc_images` WHERE `saved` = 0 LIMIT 1");
		var_dump($imageRow); 
		if (!$imageRow)
			exit;

		$post_id = $imageRow->post_id;
		$url = $imageRow->url;

		$attach_id = $this->saveAttachment($post_id, $url);
		if ($attach_id) {
			$wpdb->query("UPDATE `wp_mc_images` SET `saved` = " . intval($attach_id) . " WHERE id = " . $imageRow->id);

			$images = $wpdb->get_col("SELECT saved FROM `wp_mc_images` WHERE `post_id` = " . $post_id . " AND `saved` != 0");

			update_field('cf47rs_images', $images, $post_id);
			
			echo "Attachment saved $post_id - $attach_id";
		} else {
			$wpdb->query("DELETE FROM `wp_mc_images` WHERE id = " . $imageRow->id);
			echo "saveAttachment failed! " . $imageRow->url;
		}

		sleep(5);

    	$this->requestImageUpdate();
		exit;
	}

	public function ajaxImport()
	{
		$result = array('log' => "");

		$lastUpdate = get_option('pfi_last_update');
		$feedLink = get_option('pfi_feed_link');
		if (!$feedLink)
			exit(); 

		ob_start();

		echo "Importing properties...<br>\r\n";

		$xmlstr = file_get_contents($feedLink);
		$xml = simplexml_load_string($xmlstr);
		//if ($xml && $xml['last_update'] > $lastUpdate) {
			$this->allPostsToDraft();

			$i = 0;
			foreach ($xml->property as $key => $property)
			{
				$state = "Updated";
				$reference = UINT((string)$property->reference_number);

				$post_id = $this->getPostByReference($reference);

				$title = (string)$property->title_en;
				$content = "[vc_row][vc_column][vc_column_text]" . (string)$property->description_en . "[/vc_column_text][/vc_column][/vc_row]";

				$contract_type = 52;
				if ((string)$property->offering_type == "CR" || (string)$property->offering_type == "RR")
					$contract_type = 50;

				$type = 0;
				if ((string)$property->offering_type == "CR" || (string)$property->offering_type == "CS")
					$type = 1;


				$property_type = 11;
				switch ((string)$property->property_type) {
					case 'AP':
						$property_type = 11;
						break;
					case 'PH':
						$property_type = 47;
						break;
					case 'LP':
						$property_type = 69;
						break;
					case 'TH':
						$property_type = 61;
						break;
					case 'VH':
						$property_type = 67;
						break;
					default:
						$property_type = 11;
						break;
				}

				
				$price_on_application = strtolower((string)$property->price_on_application);
				$price = UINT((string)$property->price);
				if ($price_on_application == "yes")
					$price = 0;
				$area = UINT((string)$property->size);
				$bathrooms = UINT((string)$property->bathroom);
				$bedrooms = UINT((string)$property->bedroom);
				$rooms = $bedrooms + 1;
				$garages = UINT((string)$property->parking);
				$location = strtoupper((string)$property->community);
				$map_location = (string)$property->geopoints;

				if (!$post_id) {
					$newPost = array (
					  'post_title'    => $title,
					  'post_content'  => $content,
					  'post_status'   => 'publish',
					  'post_author'   => 1,
					  'post_type'	  => 'cf47rs_property',
					);

					$post_id = wp_insert_post($newPost);
					$state = "Added";
				} else {
					$args = array(
				    	'ID'          => $post_id,
				    	'post_status' => 'publish',
					);
					wp_update_post($args);
				}

				$amenities = explode(",", (string)$property->amenities);
				global $IDS_Amenities;
				$amenities_terms = array();
				foreach ($amenities as $key => $value) {
					if ($IDS_Amenities[$value])
						$amenities_terms[] = $IDS_Amenities[$value];
				}

				if ($amenities_terms)
					wp_set_object_terms($post_id, $amenities_terms, 'cf47rs_property_feature');

				if ($location)
					wp_set_object_terms($post_id, $location, 'cf47rs_property_location');

				update_field('cf47rs_contract_type', $contract_type, $post_id);
				update_field('cf47rs_property_type', $property_type, $post_id);

				update_field('cf47rs_sku', $reference, $post_id);
				update_field('cf47rs_price', $price, $post_id);
				update_field('cf47rs_area', $area, $post_id);
				update_field('cf47rs_bathrooms', $bathrooms, $post_id);
				update_field('cf47rs_bedrooms', $bedrooms, $post_id);
				update_field('cf47rs_rooms', $rooms, $post_id);
				update_field('cf47rs_garages', $garages, $post_id);

				update_field('cf47rs_featured', $i > 6 ? 0 : 1, $post_id);

				if ($map_location) {
					$coordinates = explode(",", $map_location);
					if (count($coordinates) > 1) {
						$mapArray = array (
						  'address' => (string)$property->property_name,
						  'lat' => $coordinates[1],
						  'lng' => $coordinates[0],
						);

						update_field('cf47rs_map_location', $mapArray, $post_id);
					}
				}

				$this->clearImagesQueue($post_id);
				foreach ($property->photo->url as $key => $url)
				{
					echo "Image: $url<br>";
					$this->addImageQueue($post_id, (string)$url);
				}

				echo "$state: $title<br>";
				$i++;
			}

		//	update_option('pfi_last_update', (string)$xml['last_update']);
		//	$lastUpdate = get_option('pfi_last_update');
		//}

		echo "Done<br>\r\n";

		$debug = ob_get_clean();

		if ($debug)
			$result['log'] .= $debug;

		$this->requestImageUpdate();

		echo json_encode($result);
		exit();
	}

	private function allPostsToDraft()
	{
		$args = array(
			'post_status'   => array('publish'),
		    'numberposts'	=> -1,
			'post_type'		=> 'cf47rs_property',
		);
		$posts = get_posts($args);
		foreach ($posts as $key => $post)
		{
			$newProperty = false;
			$terms = wp_get_post_terms($post->ID, 'cf47rs_property_tag');
			foreach ($terms as $key => $term) {
				if ($term->slug == "new-properties")
					$newProperty = true;
			}
			if ($newProperty)
				continue;

			$args = array(
		    	'ID'          => $post->ID,
		    	'post_status' => 'draft',
			);
			wp_update_post($args);
		}
	}

	private function getPostByReference($reference)
	{
		$args = array(
			'post_status'   => array('publish', 'draft'),
		    'numberposts'	=> 1,
			'post_type'		=> 'cf47rs_property',
			'meta_query'	=> array(
				array(
					'key'	  	=> 'cf47rs_sku',
					'value'	  	=> $reference,
					'compare' 	=> '=',
				),
			),
		);
		$posts = get_posts($args);
		if ($posts)
			return $posts[0]->ID;

		return NULL;
	}


    private function saveAttachment($post_id, $url)
    {
        $upload_dir = wp_upload_dir();
		$title = md5($url);
        $file = $title . '.jpg';
		$filename = $upload_dir['basedir'] . '/properties/' . $file;
		
		$imageSizes = ["-1024x681", "-1170x600-c-center", "-150x150", "-1740x960-c-center", "-270x180-c-center", "-300x200", "-768x511", "-800x0-c-center"];

		if (file_exists($filename)) {
			$attach_id = $this->getImageId('properties/' . $file);

			if (!$attach_id)
			{
				echo "File '$url' exists without attachment! Removing<br>\r\n";
				unlink($filename);
				
				foreach ($imageSizes as $imageSize)
				{
					$subFileName = $upload_dir['basedir'] . '/properties/' . $title . $imageSize . "jpg";
					if (file_exists($subFileName))
						unlink($subFileName);
				}
			}
			else {
				echo "File '$url' exists! Returning attach_id : $attach_id<br>\r\n";
				return $attach_id;
			}
		}

		//file_put_contents($filename, file_get_contents($url));
		if (!$this->downloadFile($filename, $url))
		{
			echo "Failed to download '$url'. Returning false<br>\r\n";
			return false;
		}

        $wp_filetype = wp_check_filetype(basename($filename), null);

        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content' => '',
            'post_status' => 'inherit',
            'post_parent' => $post_id
        );

        $attach_id = wp_insert_attachment($attachment, $filename, $post_id);

		$attach_data = wp_generate_attachment_metadata($attach_id, $filename);
		wp_update_attachment_metadata($attach_id, $attach_data);
		
		echo "File $url downloaded! Returning attach_id : $attach_id<br>\r\n";

        return $attach_id;
		
    }

	private function getImageId($image_url) {
	    global $wpdb;
	    $attachment = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_value='%s';", $image_url)); 
	        return $attachment[0]; 
	}

	private function downloadFile($path, $url)
	{
	    $newfname = $path;
	    $file = fopen ($url, 'rb');
	    if ($file) {
	        $newf = fopen ($newfname, 'wb');
	        if ($newf) {
	            while(!feof($file)) {
	                fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
	            }
	        }
	    }
	    if ($file) {
	        fclose($file);
	    }
	    if ($newf) {
	        fclose($newf);
	    }

	    if (!$file || !$newf)
	    	return false;

	    return true;
	}

	private function clearImagesQueue($post_id) {
		global $wpdb;

		$wpdb->query("DELETE FROM `wp_mc_images` WHERE `post_id` = $post_id");
	}

	private function addImageQueue($post_id, $url) {
		global $wpdb;

		$imageRow =$wpdb->get_row($wpdb->prepare("SELECT * FROM `wp_mc_images` WHERE post_id = '%d' AND `url` = '%s';", $post_id, $url));
		if ($imageRow)
			return;

		$wpdb->insert('wp_mc_images', 
			array( 
				'post_id' => $post_id, 
				'url' => $url
			), 
			array( 
				'%d', 
				'%s' 
			) 
		);
	}
}

$mysrcImporter = new IDS_MycrmImporter();
$mysrcImporter->run();

register_activation_hook(__FILE__, array('IDS_MycrmImporter', 'install'));