<li class="wis-item">
    <a href="<?php echo get_admin_url() . 'upload.php?item=' . $atts['data']['id'] ?>" target="_blank">
        <img src="<?php echo wp_get_attachment_image_url($atts['data']['id']) ?>" alt="">
    </a>
    <h4>Filename: <?php echo get_the_title($atts['data']['id']) ?></h4>
    <h4>
        Original:
        <a href="<?php echo get_post_meta($atts['data']['id'], 'original_url', true) ?>" target="_blank">
            <?php echo get_post_meta($atts['data']['id'], 'user_name', true) ?>
        </a>
    </h4>
    <h4>
        Exist: 

        <?php 
        
            if ($atts['data']['exist']) {
                echo __('Downloaded', WIS) . ' ' . get_the_date('d.m.Y', $atts['data']['id']);
            } else {
                _e('New download', WIS);
            }
        
        ?>

    </h4>
</li>
<!-- https://www.instagram.com/p/CQCMyDZndR5/?utm_source=ig_web_copy_link, https://www.instagram.com/p/CQBRzWqB6qG/?utm_source=ig_web_copy_link -->