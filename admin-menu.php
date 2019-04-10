<?php
add_action('admin_menu', 'csv_export_menu');
function csv_export_menu(){
   add_menu_page('CSV EXPORT', 'CSV EXPORT', 'manage_options' , 'csv-export', 'csv_export' );
   // add_submenu_page('csv_import', 'Import Locations', 'Post Children', 'manage_options', 'post_children', 'import_children_posts' );
   // add_submenu_page('my-menu', 'Submenu Page Title2', 'Whatever You Want2', 'manage_options', 'my-menu2' );
}

function csv_export(){
  $folder = $_SERVER['DOCUMENT_ROOT'].'/wordpress/wp-content/plugins/csv_export/backup_files/';
  // $folder = plugin_dir_url(__FILE__).'backup_files';
  $file = $folder.'report.csv';
  $report_file = fopen($file,'w+');

  fputcsv($report_file,array('ID','INCIDENT-TITLE','DESCRIPTION','INCIDENT-DATE','REPORT-TYPE','VICTIMS','LOCATION'),',');

  $args = array(
  'posts_per_page' => 100,
	'post_type' => 'reports',
  );

  $the_query = new WP_Query( $args );

  // The Loop
  if ( $the_query->have_posts() ) {

      while ( $the_query->have_posts() ) {
          $the_query->the_post();
          $post_id = get_the_ID();
          $post_title = get_the_title();
          $post_desc = get_the_content();
          $post_date = get_the_date();

          $rowCsv = array(
            $post_id,
            $post_title,
            $post_desc,
            $post_date
          );

          $taxonomies = array(
            'report-type' => array(),
            'victims'     => array(),
            'locations'   => array()
          );

          foreach ( $taxonomies as $key => $value ) {

            $terms = wp_get_post_terms( get_the_ID(), $key );

            foreach ( $terms as $term ){
              array_push( $taxonomies[ $key], $term->name );
            }//terms

            array_push( $rowCsv, implode( ',', $taxonomies[ $key ] ) );

          }//taxonomies


          echo '<pre>';
          print_r( $rowCsv );
          echo '</pre>';



          fputcsv( $report_file, $rowCsv, ',' );


      }//while loop

      /* Restore original Post Data */
      wp_reset_postdata();
  } else {
      // no posts found
  }
  fclose($report_file);
}
