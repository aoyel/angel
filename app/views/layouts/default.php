
<head>
This is head
</head>

<?php echo $this->beginHead();?>

<?php echo $this->endHead();?>

<?php echo $this->beginContent();?>

<?php
	echo $this->content;
?>
<?php echo $this->endContent();?>
<footer>
This is footer
</footer>