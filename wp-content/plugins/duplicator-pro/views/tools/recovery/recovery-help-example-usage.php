<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?><p>
    <?php DUP_PRO_U::esc_html_e('The following is a typical scenario showing how one can quickly restore the site after a bad plugin update using the Recovery Point:'); ?>
<ol>
    <li><?php DUP_PRO_U::esc_html_e('User builds an unfiltered package.');?></li>
    <li><?php DUP_PRO_U::esc_html_e('User sets the Recovery Point on that package (Hamburger menu on package row.)'); ?></li>
    <li><?php DUP_PRO_U::esc_html_e('User copies the Recovery URL to the clipboard and pastes into a text file or other safe spot.'); ?></li>
    <li><?php DUP_PRO_U::esc_html_e('User updates plugins.');?></li>
    <li><?php DUP_PRO_U::esc_html_e('** Site crashes due to bad code in a plugin update **');?></li>
    <li><?php DUP_PRO_U::esc_html_e('User pastes Recovery URL into a browser and quickly restores the site using the streamlined installer.');?></li>
</ol>

<?php DUP_PRO_U::esc_html_e('After the above sequence occurs, the site has been restored with the site experiencing minimal downtime.');?>
</p>