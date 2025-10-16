<?php

$foogallery_plans = array(
	FOOGALLERY_PRO_PLAN_STARTER  => __( 'PRO Starter', 'foogallery' ),
	FOOGALLERY_PRO_PLAN_EXPERT   => __( 'PRO Expert', 'foogallery' ),
	FOOGALLERY_PRO_PLAN_COMMERCE => __( 'PRO Commerce', 'foogallery' ),
);

$foogallery_pro_features = foogallery_pro_features();

?>

<div id="pro_section" class="foogallery-admin-help-section" style="display: none">
	<section class="fgah-feature">
		<header>
			<h3><?php esc_html_e( 'FooGallery PRO Plans', 'foogallery' );?></h3>
			<p><?php esc_html_e( 'Choose from a PRO Plan that suits your requirements and budget : ', 'foogallery' );?>
				<span class="fgah-plan-prostarter"><?php esc_html_e( 'PRO Starter', 'foogallery' );?></span>,
				<span class="fgah-plan-pro"><?php esc_html_e( 'PRO Expert', 'foogallery' );?></span> <?php esc_html_e( 'or', 'foogallery' );?>
				<span class="fgah-plan-commerce"><?php esc_html_e( 'PRO Commerce', 'foogallery' );?></span>
			</p>
		</header>
		<footer>
			<a class="foogallery-admin-help-button-cta" href="<?php echo esc_url ( $plans_url ); ?>"><?php esc_html_e( 'Compare FooGallery PRO Plans', 'foogallery' ); ?></a>
		</footer>
	</section>
    <section class="fgah-feature">
        <?php if ( $show_trial_message ) { ?>
            <header>
                <h3><?php esc_html_e( 'FooGallery PRO Free Trial 🤩', 'foogallery' );?></h3>
                <p><?php esc_html_e( 'Want to test out all the PRO features below? No problem! You can start a 7-day free trial immediately!', 'foogallery' );?></p>
            </header>
            <footer>
                <a class="foogallery-admin-help-button-cta" href="<?php echo esc_url ( foogallery_admin_freetrial_url() ); ?>"><?php esc_html_e( 'Start Your 7-day Free Trial', 'foogallery' ); ?></a>
            </footer>
        <?php } else if ( $show_thanks_for_pro ) { ?>
            <header>
                <h3><?php echo sprintf( esc_html__( 'Thanks for your support by purchasing a %s license 😍', 'foogallery' ), '<span class="fgah-plan-' . esc_attr( $foogallery_current_plan ) . '">' . esc_html( $foogallery_plans[ $foogallery_current_plan ] ) . '</span>' );?></h3>
                <p><?php esc_html_e( 'Check out the PRO features you can start using immediately...', 'foogallery' );?></p>
            </header>
        <?php } else if ( $is_trial ) { ?>
            <header>
                <h3><?php esc_html_e( 'Thanks for trying out PRO 😍', 'foogallery' );?></h3>
                <p><?php esc_html_e( 'Check out the PRO features you can try out immediately...', 'foogallery' );?></p>
            </header>
        <?php } ?>
    </section>

	<?php
    $i = -1;
    foreach ( $foogallery_pro_features as $feature ) {
        if ( isset( $feature['hide_from_help'] ) && $feature['hide_from_help'] === true ) {
            continue;
        }
        $i++;
        ?>
    <section class="fgah-feature fgah-feature-pro<?php echo ( $i % 2 === 0 ) ? " fgah-feature-right" : ""; ?> fgah-feature-plan-<?php echo esc_attr( $feature['plan'] ); ?>">
        <div>
            <figure>
                <a href="<?php echo esc_url( foogallery_admin_url( $feature['link'], 'help', $feature['utm_content'] ) ); ?>" target="_blank">
                    <img src="<?php echo esc_url( $feature['image'] ); ?>" alt="<?php echo esc_html( $feature['title'] ); ?>" />
                </a>
            </figure>
            <dl>
                <dt><?php echo esc_html( $feature['title']); ?></dt>
                <dd>
	                <div class="fgah-feature-sub-title">
		                <h4><?php esc_html_e( 'Available in:', 'foogallery' ); ?></h4>
		                <?php
		                $available_plans = array();
		                foreach ( $feature['plans'] as $plan ) {
			                $available_plans[] = '<span class="fgah-plan-' . esc_attr( $plan ) . '">' . esc_html( $foogallery_plans[ $plan ] ) . '</span>';
		                }
		                echo wp_kses_post( implode( ' ', $available_plans ) );
	                    ?>
	                </div>
	                <p>
		                <?php echo esc_html( $feature['desc'] ); ?>
	                </p>
	                <p>
                        <a href="<?php echo esc_url( foogallery_admin_url( $feature['link'], 'help', $feature['utm_content'] ) ); ?>"
                           target="_blank"><?php echo esc_html( $feature['link_text']); ?></a>
	                </p>
                </dd>
            </dl>
        </div>
    </section>
    <?php } ?>
</div>