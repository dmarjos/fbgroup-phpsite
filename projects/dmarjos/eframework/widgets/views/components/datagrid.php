<div class="datagrid" id="<?php echo $id?>_grid"></div>
<script type="text/javascript">
var <?php echo $id?>Grid; 

$(document.body).ready(function() {
	<?php echo $id?>Grid=new jsFw.tools.datagrid('<?php echo $id?>_grid');
	<?php if ($init!="") { echo $init."();"; }?>

	<?php echo $id?>Grid.init();
});
</script>