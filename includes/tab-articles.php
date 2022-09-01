<?php
$query = $this->get_local_file_contents('includes/sql/articles_migrate.sql');

$all = get_posts(
    array(
        'post_type'=> 'post',
        'numberposts'=> -1, 
        'orderby'=> 'post_title', 
        'order'=> 'asc',
        'post_status' => array(
            'publish', 
            'pending', 
            'draft', 
            'auto-draft', 
            'future', 
            'private', 
            'inherit', 
            'trash',
        ),
    )
);
?>
<style>
  .accordion                { border: solid 1px #ccc; width: 100%;height: 100%; min-height: 100px; max-height: 600px; overflow: scroll; }
  .accordion .item          { border-bottom: solid 1px #ccc; }
  .accordion .item .heading { padding: 10px; cursor: pointer;}
  .accordion .item .body    { border-top: solid 1px #ccc; padding: 10px; display: none; }
  select                    {width:100%; height: 100%; max-width: none;}
</style>
<table width="100%" style="table-layout: fixed;">
    <tr>
        <td valign="top">
            <h2>Drupal</h2>
        </td>
        <td valign="top">
            <h2>Wordpress</h2>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <?php 
            if(isset($_GET['insert_articles'])){ 
                $this->wp_insert_posts($query,'articles');
            }
            ?>
        </td>
        <td valign="top">
            <?php 
            if(isset($_GET['delete_articles'])){
                $this->wp_delete_posts($all);
            }
            ?>
        </td>
    </tr>
    <tr>
        <td>
            <b>Total records in Drupal:</b> <?php echo $this->drupal_get_count($query); ?><br>
            <a href="<?php echo $admin_url; ?>&insert_articles=true&dry_run=true" class="button button-primary">Insert Into WP (Dry Run)</a>
            <a href="<?php echo $admin_url; ?>&insert_articles=true&dry_run=false" onclick="return confirm('Are you sure you want to insert all drupal records?');" class="button button-primary">Insert Into WP</a>
        </td>
        <td>
            <b>Total records in Wordpress:</b> <?php echo count($all); ?><br>
            <a href="<?php echo $admin_url; ?>&delete_articles=true" class="button button-primary">Delete All From WP</a>
        </td>
    </tr>
</table>
<script type="text/javascript">
    jQuery(document).ready(function($){
        $('#results').on('change', function(){
            var id = $(this).val();
            document.location.href = '<?php echo $admin_url; ?>&insert_articles&dry_run=true&results='+id;
        });
        $('.heading').on('click', function(){
          var $body = $(this).siblings(".body");
          $body.toggle();
        });

    });
</script>


