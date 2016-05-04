<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
?>

<h1>Color palette</h1>

<?php foreach($colors as $color): ?>
    <div style="border-top:1px solid #ccc;clear:left;">
        <h2><?php echo $color; ?></h2>
        <?php for($i = -95; $i < 96; $i += 5): 
            $hex = $palette->get($color, $i);
        ?>
            <div style="float:left">
                <div style="height:42px;width:42px;background-color:<?php echo $hex; ?>;"></div>
                <div style="font-size:12px;margin-top:5px;text-align:center;"><?php echo $hex; ?></div>
                <div style="font-size:10px;margin-top:5px;text-align:center;color:#999;">
                    <?php
                        if($i < 0){ echo '-'.abs($i); }
                        else if($i == 0){ echo 'R'; }
                        else { echo '+'.$i; }
                     ?>
                </div>
            </div>
        <?php endfor; ?>
    </div>
<?php endforeach; ?>

<div style="clear:left;">
<h1>Color grid</h1>
    <?php foreach($colors as $color): ?>
        <div style="clear:left;font-size:7px;font-family:Arial, Helvetice, sans-serif;color:gray;"><?php echo mb_strtoupper($color); ?></div>
        <div style="clear:left;">
            <?php for($i = -95; $i < 96; $i += 5): 
                $hex = $palette->get($color, $i);
            ?>
                <div style="float:left">
                    <div style="height:20px;width:20px;background-color:<?php echo $hex; ?>;"></div>
                    <div style="margin:1px 0 3px 0;font-size:7px;text-align:center;font-family:Arial, Helvetice, sans-serif;color:#ccc;">
                        <?php
                            if($i < 0){ echo '-'.abs($i); }
                            else if($i == 0){ echo 'R'; }
                            else { echo '+'.$i; }
                         ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    <?php endforeach; ?>
</div>
<br />
<br />
