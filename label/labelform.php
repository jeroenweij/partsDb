<?php

$labelformscript = "<script>
function target_popup(form) {
    window.open('', 'formpopup', 'width=400,height=400,resizeable,scrollbars');
    form.target = 'formpopup';
}
</script>";

function printprintbutton($id, $mpn, $type, $value, $location): void
{
    ?>
    <div>
        <form action="label/printlabel.php" method="post" onsubmit="target_popup(this)">
            <input type="hidden" name="id" value="<?php echo($id); ?>"/>
            <input type="hidden" name="mpn" value="<?php echo($mpn); ?>"/>
            <input type="hidden" name="type" value="<?php echo($type); ?>"/>
            <input type="hidden" name="value" value="<?php echo($value); ?>"/>
            <input type="hidden" name="loc" value="<?php echo($location); ?>"/>
            <input name="save" type="submit" value="Print label"/>
        </form>
    </div>
    <?php
}

?>