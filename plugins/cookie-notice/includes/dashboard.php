<?php

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Cookie_Notice_Dashboard class.
 * 
 * @class Cookie_Notice_Dashboard
 */
class Cookie_Notice_Dashboard {

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		// actions
		add_action( 'wp_dashboard_setup', [ $this, 'wp_dashboard_setup' ], 11 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts_styles' ] );
	}

	/**
	 * Initialize widget.
	 *
	 * @return void
	 */
	public function wp_dashboard_setup() {
		// filter user_can_see_stats
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			return;
		
		global $wp_meta_boxes;

		$widget_key = 'cn_dashboard_stats';

		// add dashboard chart widget
		wp_add_dashboard_widget( $widget_key, __( 'Cookie Compliance', 'cookie-notice' ), [ $this, 'dashboard_widget' ] );
		
		// attempt to place the widget at the top
		$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

		$widget_instance  = array( $widget_key => $normal_dashboard[ $widget_key ] );
		unset( $normal_dashboard[ $widget_key ] );
		$sorted_dashboard = array_merge( $widget_instance, $normal_dashboard );

		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $pagenow
	 * @return void
	 */
	public function admin_scripts_styles( $pagenow ) {
		if ( $pagenow !== 'index.php' )
			return;

		// filter user_can_see_stats
		if ( ! current_user_can( apply_filters( 'cn_manage_cookie_notice_cap', 'manage_options' ) ) )
			return;

		$analytics = get_option( 'cookie_notice_app_analytics' );
		$date_format = get_option( 'date_format' );

		// styles
		wp_enqueue_style( 'cn-admin-dashboard', plugins_url( '../css/admin-dashboard.css', __FILE__ ), [], Cookie_Notice()->defaults['version'] );
		wp_enqueue_style( 'cn-microtip', plugins_url( '../assets/microtip/microtip.min.css', __FILE__ ), [], Cookie_Notice()->defaults['version'] );
		
		// bail if COmpliance is not active
		if ( Cookie_Notice()->get_status() !== 'active' )
			return;

		// scripts
		wp_enqueue_script( 'cn-admin-chartjs', plugins_url( '../assets/chartjs/chart.min.js', __FILE__ ), [ 'jquery' ], Cookie_Notice()->defaults['version'], true );
		wp_enqueue_script( 'cn-admin-dashboard', plugins_url( '../js/admin-dashboard.js', __FILE__ ), [ 'jquery', 'cn-admin-chartjs' ], Cookie_Notice()->defaults['version'], true );
		
		// cycle usage data
		$cycle_usage = array(
			'threshold' => ! empty( $analytics['cycleUsage']->threshold ) ? (int) $analytics['cycleUsage']->threshold : 0,
			'visits' => ! empty( $analytics['cycleUsage']->visits ) ? (int) $analytics['cycleUsage']->visits : 0,
			'days_to_go' => ! empty( $analytics['cycleUsage']->daysToGo ) ? (int) $analytics['cycleUsage']->daysToGo : 0,
			'start_date' => ! empty( $analytics['cycleUsage']->startDate ) ? date_i18n( $date_format, strtotime( $analytics['cycleUsage']->startDate ) ) : '',
			'end_date' => ! empty( $analytics['cycleUsage']->endDate ) ? date_i18n( $date_format, strtotime( $analytics['cycleUsage']->endDate ) ) : ''
		);
		
		// available visits, -1 for no pro plans
		$cycle_usage['visits_available'] = $cycle_usage['threshold'] ? $cycle_usage['threshold'] - $cycle_usage['visits'] : -1;
		$cycle_usage['threshold_used'] = $cycle_usage['threshold'] ? ( $cycle_usage['visits'] / $cycle_usage['threshold'] ) * 100 : 0;
		$cycle_usage['threshold_used'] = $cycle_usage['threshold_used'] > 100 ? 100 : $cycle_usage['threshold_used'];
		
		$chartdata = [
			'usage' => array(
				'type' => 'doughnut',
				'data' => array(
					'labels' => array(
						_x( 'Used', 'threshold limit', 'cookie-notice' ),
						_x( 'Free', 'threshold limit', 'cookie-notice' )
					),
					'datasets' => array(
						array(
							'data' => array( $cycle_usage['visits'], $cycle_usage['visits_available'] ),
							// 'borderColor' => 'rgb(255, 255, 255)',
							'backgroundColor' => array(
								'rgb(32, 193, 158)',
								'rgb(235, 233, 235)'
							)
						)
					)
				)
			),
			'consent-activity' => array(
				'type' => 'line'
			)
		];
		
		// warning usage color
		if ( $cycle_usage['threshold_used'] > 80 && $cycle_usage['threshold_used'] < 100 ) {
			$chartdata['usage']['data']['datasets'][0]['backgroundColor'][0] = 'rgb(255, 193, 7)';
		// danger usage color
		} elseif ( $cycle_usage['threshold_used'] == 100 ) {
			$chartdata['usage']['data']['datasets'][0]['backgroundColor'][0] = 'rgb(220, 53, 69)';
		}
		
		// echo '<pre>'; print_r( $analytics ); echo '</pre>';	exit;

		if ( $analytics['consentActivities'] && is_array( $analytics['consentActivities'] ) ) {

			$data = array(
			);

			// set default color;
			$color = [
				'r' => 32,
				'g' => 193,
				'b' => 158
			];

			$data = [
				'labels' => [],
				'datasets' => [
					0 => array(
						'label' => sprintf( __( 'Level %s', 'cookie-notice' ), 1 ),
						'data' => array(),
						'fill' => true,
						'backgroundColor' => 'rgba(196, 196, 196, 0.3)',
						'borderColor' => 'rgba(196, 196, 196, 1)',
						'borderWidth' => 1.2,
						'borderDash' => [],
						'pointBorderColor' => 'rgba(196, 196, 196, 1)',
						'pointBackgroundColor' => 'rgba(255, 255, 255, 1)',
						'pointBorderWidth' => 1.2
					),
					1 => array(
						'label' => sprintf( __( 'Level %s', 'cookie-notice' ), 2 ),
						'data' => array(),
						'fill' => true,
						'backgroundColor' => 'rgba(213, 181, 101, 0.3)',
						'borderColor' => 'rgba(213, 181, 101, 1)',
						'borderWidth' => 1.2,
						'borderDash' => [],
						'pointBorderColor' => 'rgba(213, 181, 101, 1)',
						'pointBackgroundColor' => 'rgba(255, 255, 255, 1)',
						'pointBorderWidth' => 1.2
					),
					2 => array(
						'label' => sprintf( __( 'Level %s', 'cookie-notice' ), 3 ),
						'data' => array(),
						'fill' => true,
						'backgroundColor' => 'rgba(152, 145, 177, 0.3)',
						'borderColor' => 'rgba(152, 145, 177, 1)',
						'borderWidth' => 1.2,
						'borderDash' => [],
						'pointBorderColor' => 'rgba(152, 145, 177, 1)',
						'pointBackgroundColor' => 'rgba(255, 255, 255, 1)',
						'pointBorderWidth' => 1.2
					),
				]
			];
			
			// generate chart days
			$chart_date_format = 'j/m';

			for( $i = 30; $i > 0; $i-- ) {
				$data['labels'][] = date( $chart_date_format , strtotime( '-'. $i .' days' ) );
				
				$data['datasets'][0]['data'][] = 0;
				$data['datasets'][1]['data'][] = 0;
				$data['datasets'][2]['data'][] = 0;
			}

			// set consent records in charts days
			foreach ( $analytics['consentActivities'] as $index => $entry ) {
				$time = date_i18n( $chart_date_format, strtotime( $entry->eventdt ) );
				$records = $entry->totalrecd;
				$level = $entry->consentlevel;

				$i = array_search( $time, $data['labels'] );

				if ( $i )
					$data['datasets'][$level - 1]['data'][$i] = $records;
			}

			$chartdata['consent-activity']['data'] = $data;
		}

		// echo '<pre>'; print_r( $chartdata ); echo '</pre>'; exit;

		wp_localize_script(
				'cn-admin-dashboard',
				'cnDashboardArgs',
				[
					'ajaxURL' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'cn-dashboard-widget' ),
					'nonceUser' => wp_create_nonce( 'cn-dashboard-user-options' ),
					'charts' => $chartdata
				]
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @return void
	 */
	public function dashboard_widget() {
		$html = '';

		// Compliance active, display chart
		if ( Cookie_Notice()->get_status() == 'active' ) {
			// get user options
			$user_options = get_user_meta( get_current_user_id(), 'pvc_dashboard', true );

			// empty options?
			if ( empty( $user_options ) || ! is_array( $user_options ) )
				$user_options = [];

			// sanitize options
			$user_options = map_deep( $user_options, 'sanitize_text_field' );

			// get menu items
			$menu_items = ! empty( $user_options['menu_items'] ) ? $user_options['menu_items'] : [];

			$items = [
				[
					'id' => 'visits',
					'title' => __( 'Traffic Overview', 'cookie-notice' ),
					'description' => __( 'Displays the general visits information for your domain.', 'cookie-notice' )
				],
				[
					'id' => 'consent-activity',
					'title' => __( 'Consent Activity', 'cookie-notice' ),
					'description' => __( 'Displays the chart of the domain consent activity in the last 30 days.', 'cookie-notice' )
				],
			];

			$html = '
			<div id="cn-dashboard-accordion" class="cn-accordion">';

			foreach ( $items as $item ) {
				$html .= $this->widget_item( $item, $menu_items );
			}

			$html .= '
			</div>';
		// Compliance inactive, display image
		} else {
			$html = '
			<div id="cn-dashboard-accordion" class="cn-accordion cn-widget-block">
				<img src="' . plugins_url( '../img/cookie-compliance-widget.png', __FILE__ ) . '" alt="Cookie Compliance widget" />
				<div id="cn-dashboard-upgrade">
					<div id="cn-dashboard-modal">
						<h2>' . __( 'View consent activity inside WordPress Dashboard', 'cookie-notice' ) . '</h2>
						<p>' . __( 'Display information about the visits.', 'cookie-notice' ) . '</p>
						<p>' . __( 'Get Consent logs data for the last 30 days.', 'cookie-notice' ) . '</p>
						<p>' . __( 'Enable consent purpose categories, automatic cookie blocking and more.', 'cookie-notice' ) . '</p>
						<p><a href="' . admin_url( 'admin.php' ) . '?page=cookie-notice&welcome=1' . '" class="button button-primary button-hero cn-button">' . __( 'Upgrade to Cookie Compliance', 'cookie-notice' ) . '</a></p>
					</div>
				</div>
			</div>';
		}	
		

		echo $html;
	}

	/**
	 * Generate dashboard widget item HTML.
	 *
	 * @param array $item
	 * @param array $menu_items
	 * @return string
	 */
	public function widget_item( $item, $menu_items ) {
		// allows a list of HTML Entities such as  
		$allowed_html = wp_kses_allowed_html( 'post' );
		$allowed_html['canvas'] = [
			'id' => [],
			'height' => []
		];

		$html = '
		<div id="cn-' . esc_attr( $item['id'] ) . '" class="cn-accordion-item' . ( in_array( $item['id'], $menu_items, true ) ? ' cn-collapsed' : '' ) . '">
			<div class="cn-accordion-header">
				<div class="cn-accordion-toggle"><span class="cn-accordion-title">' . esc_html( $item['title'] ) . '</span><span class="cn-tooltip" aria-label="' . esc_html( $item['description'] ) . '" data-microtip-position="top" data-microtip-size="large" role="tooltip"><span class="cn-tooltip-icon"></span></span></div>
			</div>
			<div class="cn-accordion-content">
				<div class="cn-dashboard-container">
					<div class="cn-data-container">
						' . wp_kses( $this->widget_item_content( $item['id'] ), $allowed_html ) . '
						<span class="spinner"></span>
					</div>
				</div>
			</div>
		</div>';

		return $html;
	}

	/**
	 * Generate dashboard widget item HTML.
	 *
	 * @param array $item
	 * @param array $menu_items
	 * @return string
	 */
	public function widget_item_content( $item ) {
		$html = '';

		// get analytics data options
		$analytics = get_option( 'cookie_notice_app_analytics' );

		$date_format = get_option( 'date_format' );

		// thirty days data
		$thirty_days_usage = array(
			'visits' => ! empty( $analytics['thirtyDaysUsage']->visits ) ? (int) $analytics['thirtyDaysUsage']->visits : 0,
			'visits_updated' => ! empty( $analytics['thirtyDaysUsage']->fetchTime ) ? date_i18n( $date_format, strtotime( $analytics['thirtyDaysUsage']->fetchTime ) ) : date_i18n( $date_format, strtotime( current_time( 'mysql' ) ) ),
			'consents' => 0,
			'consents_updated' => ! empty( $analytics['lastUpdated'] ) ? date_i18n( $date_format, strtotime( $analytics['lastUpdated'] ) ) : date_i18n( $date_format, strtotime( current_time( 'mysql' ) ) )
		);

		if ( ! empty( $analytics['consentActivities'] ) ) {
			foreach ( $analytics['consentActivities'] as $index => $entry ) {
				$thirty_days_usage['consents'] += (int) $entry->totalrecd;
			}
		}

		// cycle usage data
		$cycle_usage = array(
			'threshold' => ! empty( $analytics['cycleUsage']->threshold ) ? (int) $analytics['cycleUsage']->threshold : 0,
			'visits' => ! empty( $analytics['cycleUsage']->visits ) ? (int) $analytics['cycleUsage']->visits : 0,
			'days_to_go' => ! empty( $analytics['cycleUsage']->daysToGo ) ? (int) $analytics['cycleUsage']->daysToGo : 0,
			'start_date' => ! empty( $analytics['cycleUsage']->startDate ) ? date_i18n( $date_format, strtotime( $analytics['cycleUsage']->startDate ) ) : '',
			'end_date' => ! empty( $analytics['cycleUsage']->endDate ) ? date_i18n( $date_format, strtotime( $analytics['cycleUsage']->endDate ) ) : ''
		);
		
		// available visits, -1 for no pro plans
		$cycle_usage['visits_available'] = $cycle_usage['threshold'] ? $cycle_usage['threshold'] - $cycle_usage['visits'] : -1;
		$cycle_usage['threshold_used'] = $cycle_usage['threshold'] ? ( $cycle_usage['visits'] / $cycle_usage['threshold'] ) * 100 : 0;
		$cycle_usage['threshold_used'] = $cycle_usage['threshold_used'] > 100 ? 100 : $cycle_usage['threshold_used'];

		switch ($item) {
			case 'visits' :
				$html .= '
					<div id="cn-dashboard-' . $item . '">
						<div id="cn-' . $item . '-infobox-traffic-overview" class="cn-infobox-container">
							<div id="cn-' . $item . '-infobox-visits" class="cn-infobox">
								<div class="cn-infobox-title">' . __( 'Total Visits', 'cookie-notice' ) . '</div>
								<div class="cn-infobox-number">' . number_format_i18n( $thirty_days_usage['visits'], 0 ) . '</div>
								<div class="cn-infobox-subtitle">' . __( 'Last 30 days', 'cookie-notice' ) . '</div>
							</div>
							<div id="cn-' . $item . '-infobox-consents" class="cn-infobox">
								<div class="cn-infobox-title">' . __( 'Consent Logs', 'cookie-notice' ) . '</div>
								<div class="cn-infobox-number">' . number_format_i18n( $thirty_days_usage['consents'], 0 ) . '</div>
								<div class="cn-infobox-subtitle">' . sprintf( __( 'Updated %s', 'cookie-notice' ), $thirty_days_usage['consents_updated'] ) . '</div>
							</div>
						</div>';
				
				if ( $cycle_usage['threshold'] ) {
					$usage_class = 'success';
					
					// warning usage color
					if ( $cycle_usage['threshold_used'] > 80 && $cycle_usage['threshold_used'] < 100 ) {
						$usage_class = 'warning';
					// danger usage color
					} elseif ( $cycle_usage['threshold_used'] == 100 ) {
						$usage_class = 'danger';
					}
					
					$html .= '
						<div id="cn-' . $item . '-infobox-traffic-usage" class="cn-infobox-container">
							<div id="cn-' . $item . '-infobox-limits" class="cn-infobox">
								<div class="cn-infobox-title">' . __( 'Traffic Usage', 'cookie-notice' ) . '</div>
								<div class="cn-infobox-number cn-text-' . $usage_class . '">' . number_format_i18n( $cycle_usage['threshold_used'], 1 ) . ' %</div>
								<div class="cn-infobox-subtitle">
									<p>' . sprintf( __( 'Visits usage: %1$s / %2$s', 'cookie-notice' ), $cycle_usage['visits'], $cycle_usage['threshold'] ) . '</p>
									<p>' . sprintf( __( 'Cycle started: %s', 'cookie-notice' ), date( $date_format, strtotime( $cycle_usage['start_date'] ) ) ) . '</p>
									<p>' . sprintf( __( 'Days to go: %s', 'cookie-notice' ), $cycle_usage['days_to_go'] ) . '</p>
								</div>
							</div>
							<div id="cn-' . $item . '-chart-limits" class="cn-infobox cn-chart-container">
								<canvas id="cn-usage-chart" style="height: 100px;"></canvas>
							</div>';
					
					/*
							<div id="cn-' . $item . '-traffic-notice" class="cn-infobox-notice cn-traffic-' . $usage_class . '">
								<p><b>' . __( 'Your domain has exceeded 90% of the usage limit.', 'cookie-notice' ) . '</b></p>
								<p>' . sprintf(__( 'The banner will still display properly and consent record will be set in the browser. However the Autoblocking will be disabled and Consent Records will not be stored in the application until the current visits cycle resets (in %s days).', 'cookie-notice' ), $cycle_usage['days_to_go'] ) . '</p>
							</div>
					 * 
					 */
					
					$html .= '
						</div>';
				}
				
				$html .= '
					</div>
				';

				break;

			case 'consent-activity' :
				$html .= '
					<div id="cn-dashboard-' . $item . '">
						<div id="cn-' . $item . '-chart-container cn-chart-container">
							<canvas id="cn-' . $item . '-chart" style="height: 300px;"></canvas>
						</div>
					</div>';
				break;

			default :
				break;
		}

		return $html;
	}

}
