<?php defined("ABSPATH") or die(""); ?>
<div class="section-hdr">PACKAGE CTRLS</div>

<form>
    <?php
        $CTRL['Title']  = 'duplicator_pro_package_scan';
        $CTRL['Action'] = 'duplicator_pro_package_scan';
        $CTRL['Test']   = false;
        DUP_PRO_DEBUG_TestSetup($CTRL);
    ?>
    <div class="params">
        No Params
    </div>
</form>

<!-- METHOD TEST -->
<form>
    <?php
        $CTRL['Title']  = 'DUP_PRO_CTRL_Package_addQuickFilters';
        $CTRL['Action'] = 'DUP_PRO_CTRL_Package_addQuickFilters';
        $CTRL['Test']   = true;
        DUP_PRO_DEBUG_TestSetup($CTRL);
    ?>
    <div class="params">
        <textarea style="width:200px; height: 50px" name="dir_paths">D:/path1/;
D:/path2/path/;
        </textarea>
        <textarea style="width:200px; height: 50px" name="file_paths">D:/path1/test.txt;
D:/path2/path/test2.txt;
        </textarea>
    </div>
</form>

<!-- METHOD TEST -->
<form>
    <?php
        $CTRL['Title']  = 'DUP_PRO_CTRL_Package_toggleGiftFeatureButton';
        $CTRL['Action'] = 'DUP_PRO_CTRL_Package_toggleGiftFeatureButton';
        $CTRL['nonce']  = wp_create_nonce('DUP_PRO_CTRL_Package_toggleGiftFeatureButton');
        $CTRL['Test']   = true;
        DUP_PRO_DEBUG_TestSetup($CTRL);
    ?>
    <div class="params">

        <label>Disable Gift Icon:</label>
        <input type="text" name="hide_gift_btn" value="true" /> <br/>
    </div>
</form>

