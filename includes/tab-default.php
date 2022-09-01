<table width="100%" style="table-layout: fixed;">
    <tr>
        <td valign="top">
            <h3>All Drupal Tables</h3>
            <select style="width:100%; height: 100%; max-width: none;" multiple size="20" id="selected_table" name="selected_table">
                <?php $this->drupal_get_tables($show_hidden);?>
            </select><br>
            <a href="<?php echo $admin_url; ?>&show_hidden=true">Show Hidden Tables</a><br>
        </td>
        <td valign="top">
            <h3>Records of Selected Tables</h3>
            <select style="width:100%; height: 100%; max-width: none;" multiple size="20">
            <?php
            if($selected_table){
                $this->drupal_get_records($selected_table);
            }else{
                echo '<option>No table selected yet</option>';
            }
            ?>
            </select>
        </td>
    </tr>
</table>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('#selected_table').on('change', function(){
            document.location.href = '<?php echo $this->url; ?>&selected_table='+jQuery('#selected_table').val();
        })
    });
</script>