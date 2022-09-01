<?php
/**
 * Plugin Name:   Episcopal Drupal Migration
 * Plugin URI:    https://episcopalchurch.org
 * Description:   This plugin is a tool used to migrate data from EC Drupal to EC Wordpress.
 * Version:       1.0
 * Author:        Kris Khoury
 * Author URI:    https://www.mplmnt.io
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

if ( !defined( 'ABSPATH' ) ) exit;

class drupal_migration {
    protected $files        = '/wp-content/uploads/files/';
    protected $url          = '/wp-admin/admin.php?page=ec';
    
    protected $db_user      = "root"; 
    protected $db_password  = "root"; 
    protected $db_host      = "localhost";
    protected $db_database  = "episcopalchurch_drupal";

    public $showErrors      = true;

    public $noRecords       = '<div class="alert">There are currently no records in Wordpress to delete!</div>';
    
    public function __construct()
    {
        add_action( 'admin_menu', array(
            $this,
            'wp_add_admin_page'
        ));
    }
    public function __set($property, $value)
    {
        $this->$property=$value;
    }
    public function __get($property)
    {
        return $this->$property;
    }
    public function get_local_file_contents($file_path) 
    {
        ob_start();
        include $file_path;
        $contents = ob_get_clean();
        return $contents;
    }
    public function drupal_connection() 
    {
        $mysqli = new mysqli($this->db_host, $this->db_user, $this->db_password, $this->db_database);
        if ($mysqli->connect_errno) {
            printf("Connect failed: %s\n", $mysqli->connect_error);
            exit();
        }
        return $mysqli;
    }
    public function drupal_get_count($sql)
    {
        $mysqli = $this->drupal_connection();
        $result = $mysqli->query($sql);
        $count = mysqli_num_rows($result);
        // $result->close();
        // $mysqli->close();
        return $count;
    }
    public function drupal_get_tables($show_hidden)
    {
        $mysqli     = $this->drupal_connection();
        $query      = $this->queries['allTables'];
        $result     = $mysqli->query($query);

        foreach ($result as $key => $value) {
            $zero = ($value['table_rows'] == 0);
            $disabled = ($zero) ? 'disabled' : '';
            if($show_hidden && $zero || !$zero){
                $selected = "";
                if(isset($_GET['selected_table'])){
                    $selected_table = $_GET['selected_table'];
                    if($selected_table == $value['table_name']){
                        $selected = " selected";
                    }
                }
                echo "<option $disabled $selected value='".$value['table_name']."'>";
                echo $value['table_name'];
                echo ' [' . $value['table_rows'] .']';
                echo "</option>";
            }
        }
        $mysqli->close();
    }
    public function drupal_get_records($table)
    {
        $mysqli         = $this->drupal_connection();
        $query          = "SELECT * FROM $table";

        foreach (array('utf8_general_ci') as $charset) {
            $mysqli->set_charset($charset);
            if ($result = $mysqli->query($query)) {
                $finfo = $result->fetch_all();
                foreach ($finfo as $val) {
                    echo "<option>";
                    print_r($val[0]);
                    if($val[1]){
                        echo " | ";
                        print_r($val[1]);
                    }
                    if($val[2]){
                        echo " | ";
                        print_r($val[2]);
                    }
                    echo "</option>";
                }
                $result->free();
            }
        }
        $mysqli->close();
    }
    public function drupal_get_categories_by_drupal_id($drupal_id){
        $returnArray    = array();
        $mysqli         = $this->drupal_connection();
        $query          = "SELECT name as taxonomy_name
            FROM 
              taxonomy_term_data as ttd 
            INNER JOIN 
              taxonomy_index as ti 
            ON 
              ttd.tid = ti.tid
            WHERE
            ti.nid = ".$drupal_id;
        $result         = $mysqli->query($query);

        if($result){
            while ($row = $result->fetch_object()){
                //sanitize_title
                // $term = term_exists( $row->taxonomy_name, 'category' );
                // if ( $term !== 0 && $term !== null ) {
                //     echo __( "'Uncategorized' category exists!", "textdomain" );
                // }
                array_push($returnArray, $row->taxonomy_name);
            }
        }

        $mysqli->close();
        return $returnArray;
    }
    public function wp_gutenberg_paragraph($str)
    {
        $return = '<!-- wp:paragraph -->';
        $return .= $str;
        $return .= '<!-- /wp:paragraph -->';
        return $return;
    }
    public function wp_gutenberg_image($url,$title)
    {
        $return  = '<!-- wp:image {"sizeSlug":"medium"} -->';
        $return .= '<figure class="wp-block-image size-medium">';
        $return .= '<img src="'.$this->files.$url.'" alt="'.$title.'" class="wp-image-'.rand(10000, 99999).'" />';
        $return .= '</figure>';
        $return .= '<!-- /wp:image -->';
        return $return;
    }
    public function wp_gutenberg_heading($str)
    {
        $return = '<!-- wp:heading {"level":3} -->';
        $return .= $str;
        $return .= '<!-- /wp:heading -->';
        return $return;
    }
    public function wp_add_admin_page()
    {
        add_menu_page(
            'Episcopal Drupal Migration',
            'Episcopal Drupal Migration',
            'manage_options',
            'ec',
            array(
                $this,
                'plugin_html'
            ),
            plugin_dir_url(__FILE__) . 'icon.png',
            20
        );
    }
    public function content_to_gb($obj) 
    {
        $str = '';
        if($obj->drupal_body){
            $str .= $this->wp_gutenberg_paragraph($obj->drupal_body);
        }
        if($obj->occasion_content){
            $str .= $this->wp_gutenberg_paragraph($obj->occasion_title.$obj->occasion_content);
        }
        if($obj->collect_content){
            $str .= $this->wp_gutenberg_paragraph($obj->collect_title.$obj->collect_content);
        }
        if($obj->firstlesson_subtitle){
            $str .= $this->wp_gutenberg_paragraph($obj->firstlesson_title.$obj->firstlesson_subtitle);
        }
        if($obj->firstlesson_content){
            $str .= $this->wp_gutenberg_paragraph($obj->firstlesson_content);
        }
        if($obj->oldtestament_subtitle){
            $str .= $this->wp_gutenberg_paragraph($obj->oldtestament_title.$obj->oldtestament_subtitle);
        }
        if($obj->oldtestament_content){
            $str .= $this->wp_gutenberg_paragraph($obj->oldtestament_content);
        }
        if($obj->psalm_subtitle){
            $str .= $this->wp_gutenberg_paragraph($obj->psalm_title.$obj->psalm_subtitle);
        }
        if($obj->psalm_content){
            $str .= $this->wp_gutenberg_paragraph($obj->psalm_content);
        }
        if($obj->canticle_subtitle){
            $str .= $this->wp_gutenberg_paragraph($obj->canticle_title.$obj->canticle_subtitle);
        }
        if($obj->canticle_content){
            $str .= $this->wp_gutenberg_paragraph($obj->canticle_content);
        }
        if($obj->epistle_subtitle){
            $str .= $this->wp_gutenberg_paragraph($obj->epistle_title.$obj->epistle_subtitle);
        }
        if($obj->epistle_content){
            $str .= $this->wp_gutenberg_paragraph($obj->epistle_content);
        }
        if($obj->secondlesson_subtitle){
            $str .= $this->wp_gutenberg_paragraph($obj->secondlesson_title.$obj->secondlesson_subtitle);
        }
        if($obj->secondlesson_content){
            $str .= $this->wp_gutenberg_paragraph($obj->secondlesson_content);
        }
        if($obj->gospel_subtitle){
            $str .= $this->wp_gutenberg_paragraph($obj->gospel_title.$obj->gospel_subtitle);
        }
        if($obj->gospel_content){
            $str .= $this->wp_gutenberg_paragraph($obj->gospel_content);
        }
        return $str;
    }
    public function wp_insert_post($obj)
    {
        try {
            $new_post = array(
                'post_title'        => $obj['title'],
                'post_type'         => $obj['type'],
                'post_content'      => $obj['content'],
                'post_author'       => $obj['author'],
                'post_date'         => $obj['created'],
                'post_modified'     => $obj['modified'],
                'comment_status'    => 'closed',
            );
            if(isset($obj['status'])){
                if($obj['status'] == true){
                    $new_post['post_status'] = 'publish';
                }
            }
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        } finally {
            set_time_limit(0);
            $post_id = wp_insert_post( $new_post );
            // INSERT TAXONOMIES
            if(isset($obj['tax'])){
                foreach ($obj['tax'] as $name => $value) {
                    wp_set_object_terms( $post_id, $value, $name );
                }
            }
            // INSERT FIELDS
            if(isset($obj['fields'])){
                foreach ($obj['fields'] as $name => $value) {
                    update_field( $name, $value, $post_id );
                }
            }
        }
    }
    public function wp_get_id_byslug($slug, $type){
        if ( $post = get_page_by_path($slug, OBJECT, $type ) )
            return $post->ID;
        else
            return 0;
    }
    public function wp_get_cycle_by_date($date){
        $cycle_dates = array(
            'A' => array(
                array('1995-12-1', '1996-11-30'),
                array('1998-12-1', '1999-11-30'),
                array('2001-12-1', '2002-11-30'),
                array('2004-12-1', '2005-11-30'),
                array('2007-12-1', '2008-11-30'),
                array('2010-12-1', '2011-11-30'),
                array('2013-12-1', '2014-11-30'),
                array('2016-12-1', '2017-11-30'),
                array('2019-12-1', '2020-11-30'),
                array('2022-12-1', '2023-11-30'),
                array('2025-12-1', '2026-11-30'),
            ),
            'B' => array(
                array('1996-12-1', '1997-11-30'),
                array('1999-12-1', '2000-11-30'),
                array('2002-12-1', '2003-11-30'),
                array('2005-12-1', '2006-11-30'),
                array('2008-12-1', '2009-11-30'),
                array('2011-12-1', '2012-11-30'),
                array('2014-12-1', '2015-11-30'),
                array('2017-12-1', '2018-11-30'),
                array('2020-12-1', '2021-11-30'),
                array('2023-12-1', '2024-11-30'),
                array('2026-12-1', '2027-11-30'),
            ),
            'C' => array(
                array('1997-12-1', '1998-11-30'),
                array('2000-12-1', '2001-11-30'),
                array('2003-12-1', '2004-11-30'),
                array('2006-12-1', '2007-11-30'),
                array('2009-12-1', '2010-11-30'),
                array('2012-12-1', '2013-11-30'),
                array('2015-12-1', '2016-11-30'),
                array('2018-12-1', '2019-11-30'),
                array('2021-12-1', '2022-11-30'),
                array('2024-12-1', '2025-11-30'),
                array('2027-12-1', '2028-11-30'),
            )
        );

        foreach ($cycle_dates as $key => $arr) {
            foreach ($arr as $dates) {
                if(($date >= $dates[0]) && ($date <= $dates[1])){
                    return $key;
                }
            }
        }
    }
    public function wp_insert_posts($query, $which)
    {
        try {

            $mysqli         = $this->drupal_connection();
            $result         = $mysqli->query($query);
            $rowcount       = mysqli_num_rows($result);
            $incr           = 1;

            echo '<div class="accordion">';

            if($result){
                while ($row = $result->fetch_object()){
                    $id             = $row->drupal_id;
                    $modified       = $row->wp_modified;
                    $language       = $row->drupal_language;
                    $status         = ($row->wp_status == 1) ? true : false;
                    $title          = wp_strip_all_tags($row->wp_title);
                    $created        = date('Y-m-d H:i:s',$row->wp_created);
                    $content        = $this->content_to_gb($row);

                    $taxonomies = array();
                    $fields = array();

                    switch($which){
                        case 'lectionary':
                            $type           = $row->drupal_type;
                            $date           = $row->drupal_date;
                            $date_month     = $row->drupal_date_month;
                            $date_day       = $row->drupal_date_day;
                            $author         = get_user_by('login',$row->drupal_author);
                            $user_id        = isset($author->ID) ? $author->ID : 1;
                            $translated     = $row->drupal_translated;
                            $cycle          = preg_split("/(&|,)/", $row->year_content);

                            if($translated == $id)
                                $translated = null;

                            $fields = array(
                                'field_nzWGGDyqzfcl2' => $cycle,
                                'field_60036c5592853' => sanitize_title($type),
                                'field_5ed57a9a79a9f' => $date,
                                'field_5ed57a9a79a9f_month' => $date_month,
                                'field_5ed57a9a79a9f_day' => $date_day,
                                'field_5ed477da70af7' => $id,
                                'field_5ed47ef870af8' => $translated,
                                'field_5ed5445e7b45f' => $language,
                            );
                            break;
                        case 'articles':
                            $author         = get_user_by('login',$row->drupal_author);
                            $user_id        = isset($author->ID) ? $author->ID : 1;
                            $categories     = $this->drupal_get_categories_by_drupal_id($id);
                            $taxonomies = array(
                                'category'            => $categories,
                            );
                            break;
                        case 'sermons_author':
                            $lname = $row->drupal_lname;
                            $fields = array(
                                'field_5ef2da9170e84' => $id,
                                'field_5ef2da9170ea7' => $lname
                            );
                            break;
                        case 'sermon':
                            $categories     = $this->drupal_get_categories_by_drupal_id($id);
                            $date           = $row->drupal_date;
                            $author         = get_user_by('login',$row->drupal_author);
                            $user_id        = isset($author->ID) ? $author->ID : 1;
                            $lectionary     = $row->drupal_lectionary_id;
                            $author_slug    = sanitize_title($row->drupal_actual_author);
                            $sermon_author_id      = $this->wp_get_id_byslug($author_slug, 'author');

                            if($translated == $id)
                                $translated           = null;

                            $taxonomies = array(
                                'category'            => $categories,
                                'cycle'               => $this->wp_get_cycle_by_date($date),
                            );
                            $fields = array(
                                'field_5ef2c9b76bf03' => $date,
                                'field_5ef2c9b76beaa' => $id,
                                'field_5ef2c9b76bf4e' => $language,
                                'field_5ef2c9fce8708' => $lectionary,
                                'field_5ef965b259cff' => $sermon_author_id
                            );
                            break;
                        case 'bible_study':
                            $categories     = $this->drupal_get_categories_by_drupal_id($id);
                            $date           = $row->drupal_date;
                            $author         = get_user_by('login',$row->drupal_author);
                            $user_id        = isset($author->ID) ? $author->ID : 1;
                            $lectionary     = $row->drupal_lectionary_id;
                            $author_slug    = sanitize_title($row->drupal_actual_author);
                            $sermon_author_id      = $this->wp_get_id_byslug($author_slug, 'author');

                            if($translated == $id)
                                $translated           = null;

                            $taxonomies = array(
                                'category'            => $categories,
                                'cycle'               => $this->wp_get_cycle_by_date($date),
                            );
                            $fields = array(
                                'field_5ef2c9b76bf03' => $date,
                                'field_5ef2c9b76beaa' => $id,
                                'field_5ef2c9b76bf4e' => $language,
                                'field_5ef2c9fce8708' => $lectionary,
                                'field_5ef965b259cff' => $sermon_author_id
                            );
                            break;
                        case 'staff':
                            $drupal_lname =         $row->drupal_lname;
                            $drupal_address1 =      $row->drupal_address1;
                            $drupal_address2 =      $row->drupal_address2;
                            $drupal_city =          $row->drupal_city;
                            $drupal_state =         $row->drupal_state;
                            $drupal_zip =           $row->drupal_zip;
                            $drupal_email =         $row->drupal_email;
                            $drupal_phone =         $row->drupal_phone;
                            $drupal_dept_id =       $row->drupal_dept_id;
                            $drupal_dept_name =     $row->drupal_dept_name;
                            $drupal_job_title =     $row->drupal_job_title;

                            $taxonomies = array(
                                'department'          => $drupal_dept_name,
                            );
                            $fields = array(
                                'field_staff_drupal_id' => $id,
                                'field_staff_last_name' => $drupal_lname,
                                'field_staff_address1' => $drupal_address1,
                                'field_staff_address2' => $drupal_address2,
                                'field_staff_city' => $drupal_city,
                                'field_staff_state' => $drupal_state,
                                'field_staff_zip' => $drupal_zip,
                                'field_staff_email' => $drupal_email,
                                'field_staff_phone' => $drupal_phone,
                                'field_staff_dept_name' => $drupal_dept_name,
                                'field_staff_job_title' => $drupal_job_title,
                            );
                            break;
                        case 'glossary':
                            break;
                    }

                    $obj = array(
                        'type'      => ($which == 'articles') ? 'post' : $which,
                        'created'   => $created,
                        'modified'  => $modified,
                        'title'     => $title,
                        'content'   => $content,
                        'status'    => $status,
                        'author'    => $user_id,
                        'tax'       => $taxonomies,
                        'fields'    => $fields,
                    );

                    if($_GET['dry_run'] == 'false'){
                        $this->wp_insert_post($obj);
                    }
                    ?>
                      <div class="item">
                        <div class="heading">
                          <?php echo $incr; ?>) <?php echo $row->wp_title; ?>
                        </div>
                        <div class="body">
                          <table class="table">
                            <?php
                            foreach ($row as $key => $value) {
                                ?>
                                <tr>
                                    <th valign="top"><?php echo $key; ?></th>
                                    <td><?php echo $value; ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                          </table>
                        </div>
                      </div>
                    <?php
                    $incr++;
                }
            }
            echo '</div>';

            // $result->close();
            $mysqli->close();


        } catch (Exception $e) {
            print_r($mysqli);
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    public function wp_delete_posts($all)
    {
        echo '<div class="accordion">';
        foreach ($all as $key => $post) {
            ?>
            <div class="item">
                <div class="heading">
                    <?php echo $key+1; ?>) <?php echo $post->post_title; ?>
                </div>
                <div class="body">
                    <table class="table">
                        <tr>
                            <th valign="top">ID:</th>
                            <td><?php echo $post->ID;?></td>
                        </tr>
                        <tr>
                            <th valign="top">Date:</th>
                            <td><?php echo $post->post_date;?></td>
                        </tr>
                        <tr>
                            <th valign="top">Author:</th>
                            <td><?php echo $post->post_author;?></td>
                        </tr>
                        <tr>
                            <th valign="top">Content:</th>
                            <td><div><?php echo $post->post_content;?></div></td>
                        </tr>
                        <tr>
                            <th valign="top">Slug:</th>
                            <td><?php echo $post->post_name;?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php
            wp_delete_post( $post->ID);
        }
        echo '</div>';
    }
    public function wp_insert_categories($query)
    { 
        try {
            $mysqli         = $this->drupal_connection();
            $result         = $mysqli->query($query);
            $rowcount       = mysqli_num_rows($result);
            $incr           = 1;

            echo '<div class="accordion">';

            if($result){
                while ($row = $result->fetch_object()){
                    $cat_id = 0;
                    $name = $row->taxonomy_name;
                    if($_GET['dry_run'] == 'false'){
                        $cat_id = wp_insert_category(
                            array(
                                'cat_name' => $name,
                                'category_description' => $row->taxonomy_id
                            )
                        );
                    }
                ?>
                <div class="item">
                    <div class="heading">
                      <table width="100%">
                        <tr>
                          <td width="40"><?php echo $incr; ?>)</td>
                          <td><?php echo $name; ?></td>
                          <td width="50" align="right"><?php echo $cat_id;?></td>
                        </tr>
                      </table>
                    </div>
                </div>
                <?php
                $incr++;
                }
            }
            echo '</div>';

            // $result->close();
            $mysqli->close();

        } catch (Exception $e) {
            print_r($mysqli);
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    public function wp_delete_categories($all)
    {
        foreach ($all as $cat) {
            wp_delete_category( $cat->cat_ID );
        }
    }
    public function plugin_html()
    {
        $show_hidden =          false;
        $selected_table =       false;
        $isDryRun =             false;
        $tabs =                 array(
            ''                  => 'All Drupal Tables',
            'lectionaries'      => 'Lectionaries',
            'sermons'           => 'Sermons',
            'biblestudies'      => 'Bible Studies',
            'authors'           => 'Authors',
            'articles'          => 'Articles',
            'categories'        => 'Categories',
            'staff'             => 'Staff',
            'glossary'          => 'Glossary',
            'routes'            => 'Routes',
        );

        if(isset($_GET['show_hidden'])){
            $show_hidden = $_GET['show_hidden'];
        }
        if(isset($_GET['selected_table'])){
            $selected_table = $_GET['selected_table'];
        }
        if(isset($_GET['dry_run'])){
            $isDryRun = true;
        }

        $default_tab = '';

        $tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
        $sub = isset($_GET['sub']) ? $_GET['sub'] : $default_tab;

        $admin_url = $this->url;
        if($tab!==''){
            $admin_url .= "&tab=".$tab;
        }
        if($sub!==null){
            $admin_url .= "&sub=".$sub;
        }else{
            $sub = 'migrate';
        }
        ?>
        <div class="wrap">
          <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
          <form id="ec_form" action="<?php echo $this->url; ?>" method="post">
            <h3>Drupal Tables</h3>
            <nav class="nav-tab-wrapper">
                <?php
                foreach ($tabs as $key => $item) {
                $qs = ($key != '') ? '&tab='.$key : '';
                ?>
                <a href="<?php echo $this->url.$qs; ?>" class="nav-tab <?php if($tab===$key):?>nav-tab-active<?php endif; ?>"><?php echo $item; ?></a>
                <?php } ?>
            </nav>
            <div class="tab-content">
                <?php 
                switch($tab){
                    case 'sermons':
                        include 'includes/tab-sermons.php';
                        break;
                    case 'biblestudies':
                        include 'includes/tab-biblestudies.php';
                        break;
                    case 'lectionaries':
                        include 'includes/tab-lectionaries.php';
                        break;
                    case 'authors':
                        include 'includes/tab-authors.php';
                        break;
                    case 'articles':
                        include 'includes/tab-articles.php';
                        break;
                    case 'categories':
                        include 'includes/tab-categories.php';
                        break;
                    case 'glossary':
                        include 'includes/tab-glossary.php';
                        break;
                    case 'staff':
                        include 'includes/tab-staff.php';
                        break;
                    case 'routes':
                        include 'includes/tab-routes.php';
                        break;
                    default:
                        include 'includes/tab-default.php';
                        break;
                } 
                ?>
            </div>
          </form>
        </div>
        <?php
    }
}
$dm = new drupal_migration();
?>
