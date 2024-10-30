<?php

class bolcom_Seller_Widget extends WP_Widget {
	
	function bolcom_Seller_Widget() {
		// settings	
		$widget_ops = array( 
			'classname' => 'bolcom', 
			'description' => 'Show your second hand stuff from bol.com in this widget.' );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'bolcom-seller-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'bolcom-seller-widget', 'Seller Widget - App for bol.com', $widget_ops, $control_ops );
	}

	private function zerofill( $number, $positions=2 ) {
		for($i=1;$i<$positions;$i++)	{
			if($number < pow(10,$i))
				$number = '0' . $number;
		}
		return $number;
	}

	private function getDataBolcom( $apikey, $sellerid, $amount ) {
		$transName = 'bolcom-sellerlist'; // Name of value in database.
		$cacheTime = 1; // Time in minutes between updates.
		
		if(false === ($bolcomData = get_transient($transName) ) ){
			//Get new $bolcomData
			$url = 'https://api.bol.com/rest/catalog/v4/sellerlists/'.$sellerid.'?apikey=' . $apikey . '&format=json&offset=0&limit=' . $amount;
			$response = wp_remote_get( $url, array( 'timeout' => 20 ) );

			// Check the response code
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_message = wp_remote_retrieve_response_message( $response );
		
			if ( 200 != $response_code && ! empty( $response_message ) )
				return new WP_Error( $response_code, $response_message );
			elseif ( 200 != $response_code )
				return new WP_Error( $response_code, 'Unknown error occurred' );
			
			// Get trakt data into an array.
			$bolcomData = json_decode( $response['body'], TRUE);		
			set_transient($transName, $bolcomData, 60 * $cacheTime);
		}
		return $bolcomData;
	}

	function widget( $args, $instance ) {
		extract( $args );

		// User-selected settings
		$title = apply_filters('widget_title', $instance['bolcom_title'] );
		$apikey = $instance['bolcom_apikey'];
		$sellerid = $instance['bolcom_sellerid'];
		$amount = $instance['bolcom_amount'];
		$widgetView = $instance['bolcom_widgetView'];
		
		/// Before widget (defined by themes)
		echo $before_widget;

		// Title of widget (before and after defined by themes)
		if ( $title )
			echo $before_title . $title . $after_title;

		$bolcomData = $this->getDataBolcom($apikey,$sellerid,$amount);
		 
		if ( is_wp_error( $bolcomData ) ) {
			_e('The following error occurred within the bol.com Seller Widget: ' . wp_strip_all_tags( $bolcomData->get_error_message() ), 'wordpress-bolcom');
		} else {
			// simple check to avoid errors
			/*
			echo "<pre>";
			print_r( $bolcomData );
			echo "</pre>";
			*/
			$maxActions = count($bolcomData['products']);
			
			if($maxActions < $amount) $maxcount = $maxActions; else $maxcount = $amount;
            
			for($i=0;$i<$maxcount;$i++) {
			    $comment = $bolcomData['products'][$i]['offerData']['offers'][0]['comment'];
				if(isset($bolcomData['products'][$i]['title'])) {
				    $ourl = urlencode($bolcomData['products'][$i]['urls'][0]['value'] . "prijsoverzicht/?sort=price&sortOrder=asc&filter=2ndhand");
				    $purl = "http://partnerprogramma.bol.com/click/click?p=1&s=16497&t=url&f=API&name=".urlencode($bolcomData['products'][$i]['title'])."&subid=wordpress_plugin&url=".$ourl;
					if($widgetView == 'tiled' || $widgetView == 'poster weergave' ) {				
						print '<div class="tiled"><a target="_blank" href="'.$purl.'"><img src="' . $bolcomData['products'][$i]['images'][1]['url'] . '" title="' . __('Title', 'wordpress-bolcom').': '. ucfirst($bolcomData['products'][$i]['title']) . '&#10;' . __("Price", "wordpress-bolcom").': '.str_replace(".", ",", $bolcomData['products'][$i]['offerData']['offers'][0]['price']).' euro&#10;' . __("Search for", "wordpress-bolcom").' '.$bolcomData['products'][$i]['offerData']['offers'][0]['seller']['displayName']. ' ' . __("within sellers on bol.com.", "wordpress-bolcom").'" /></a></div>';
					} else {
						print '<div class="listing"><a target="_blank" href="'.$purl.'"><img src="' . $bolcomData['products'][$i]['images'][1]['url'] . '" title="'. ucfirst(str_replace("<br/>", "&#10;", str_replace("\"", "&#34;", $bolcomData['products'][$i]['shortDescription']))) . '" /></a></div>';
						print '<div class="textf"><strong><a target="_blank" href="'.$purl.'">'.ucfirst($bolcomData['products'][$i]['title']).'</a></strong><br>'.__("Price", "wordpress-bolcom").': '.str_replace(".", ",", $bolcomData['products'][$i]['offerData']['offers'][0]['price']).' euro<br>'.__("Condition", "wordpress-bolcom").': '.$bolcomData['products'][$i]['offerData']['offers'][0]['condition'];
						if($comment != '') print '<br>'.__("Comments", "wordpress-bolcom").': '.$comment;
						print '</div><div style="clear:both;"></div>';
					}
				}
			}
		}

		// After widget (defined by themes)
		echo $after_widget;
	}
	
	function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['bolcom_title'] = strip_tags( $new_instance['bolcom_title'] );
		$instance['bolcom_apikey'] = $new_instance['bolcom_apikey'];
		$instance['bolcom_sellerid'] = $new_instance['bolcom_sellerid'];
		$instance['bolcom_amount'] = $new_instance['bolcom_amount'];
		$instance['bolcom_widgetView'] = $new_instance['bolcom_widgetView'];
		return $instance;
	}

	/**
	  * form()
	  * Displays form with options for widget instance
	  * @param mixed $instance Widget instance
	  */
	function form( $instance ) {

		// Set up some default widget settings
		$defaults = Array (
			'bolcom_title' => __('Are you buying one of my second hands books with bol.com', 'wordpress-bolcom'),
			'bolcom_apikey' => '',
			'bolcom_amount' => 2,
			'bolcom_widgetView' => ''
		);
		
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'bolcom_title' ); ?>"><?php _e('Title', 'wordpress-bolcom'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'bolcom_title' ); ?>" name="<?php echo $this->get_field_name( 'bolcom_title' ); ?>" value="<?php echo $instance['bolcom_title']; ?>" style="width:100%;" />
		</p>

		<!-- Bol.com API key: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'bolcom_apikey' ); ?>"><?php _e('Bol.com <a href="http://developers.bol.com/documentatie/aan-de-slag/" target="_blank">API key</a>', 'wordpress-bolcom'); ?>:</label> 
			<input id="<?php echo $this->get_field_id( 'bolcom_apikey' ); ?>" name="<?php echo $this->get_field_name( 'bolcom_apikey' ); ?>" value="<?php echo $instance['bolcom_apikey']; ?>" style="width:100%;" />	
		</p>

		<!-- Bol.com Seller id: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'bolcom_sellerid' ); ?>"><?php _e('Bol.com <a href="https://www.bol.com/sdd/preferences/account.html" target="_blank">selleraccount id</a>:', 'wordpress-bolcom'); ?></label> 
			<input id="<?php echo $this->get_field_id( 'bolcom_sellerid' ); ?>" name="<?php echo $this->get_field_name( 'bolcom_sellerid' ); ?>" value="<?php echo $instance['bolcom_sellerid']; ?>" style="width:100%;" />	
		</p>

		<!-- Bol.com amount of products: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'bolcom_amount' ); ?>"><?php _e('Amount of products that you want to show', 'wordpress-bolcom'); ?>:</label> 
			<input id="<?php echo $this->get_field_id( 'bolcom_amount' ); ?>" name="<?php echo $this->get_field_name( 'bolcom_amount' ); ?>" value="<?php echo $instance['bolcom_amount']; ?>" style="width:100%;" />	
		</p>
		
		<!-- What view do you want to use in the widget:  Select Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'bolcom_widgetView' ); ?>"><?php _e('The view that you want: (default: list view)', 'wordpress-bolcom'); ?></label> 
			<select name="<?php echo $this->get_field_name('bolcom_widgetView'); ?>" id="<?php echo $this->get_field_id('bolcom_widgetView'); ?>" class="widefat">
			<?php $options = array(__('list view', 'wordpress-bolcom'), __('tiled', 'wordpress-bolcom'));
			foreach ($options as $option) {
				echo '<option value="' . $option . '" id="' . $option . '"', $instance['bolcom_widgetView'] == $option ? ' selected="selected"' : '', '>', $option, '</option>';
			}
			?>
			</select>
		</p>
		<?php
	}
}

