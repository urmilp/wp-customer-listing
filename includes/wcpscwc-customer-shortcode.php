<?php
  if(isset($_POST['submit'])) {
	  
        global $wpdb;
        $table_name = $wpdb->prefix . 'datalisting';
		
        $data = array(
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'age' => $_POST['age']
           
        );
        $format = array(
            '%s',
            '%s',
			'%s'
        );
		
        $success=$wpdb->insert( $table_name, $data, $format );
        if($success){
			
            $msg = "data has been save";
			
			
        }
    } else {
       
	   
function mt_management_customers_form_shortcode(){ ?>
<form action="" id="customerForm" method="post">
<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
	<tr>
		<th colspan="2"><?php if($msg !=''){ echo $msg; } ?></th>
	</tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="name"><?php _e('Name', 'mt_management')?></label>
        </th>
        <td>
            <input id="name" name="name" type="text" style="width: 95%" value=""
                   size="50" class="code" placeholder="<?php _e('Your name', 'mt_management')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="email"><?php _e('E-Mail', 'mt_management')?></label>
        </th>
        <td>
            <input id="email" name="email" type="email" style="width: 95%" value=""
                   size="50" class="code" placeholder="<?php _e('Your E-Mail', 'mt_management')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="age"><?php _e('Age', 'mt_management')?></label>
        </th>
        <td>
            <input id="age" name="age" type="number" style="width: 95%" value=""
                   size="50" class="code" placeholder="<?php _e('Your age', 'mt_management')?>" required>
        </td>
    </tr>
	<tr class="form-field">
        <th valign="top" scope="row">
            
        </th>
        <td>
            <input name="submit" type="submit" value="Submit">
        </td>
    </tr>
    </tbody>
</table>
</form>
<?php
}
add_shortcode( 'mt_customers','mt_management_customers_form_shortcode');
    }  
?>