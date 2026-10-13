<?php
/**
 * Services: what Hassani Labs builds, each with the main technologies.
 *
 * @package Quantivista
 */

defined( 'ABSPATH' ) || exit;

$qv_services = array(
	array(
		'layout',
		__( 'Website design and development', 'quantivista' ),
		__( 'Professional, fast websites that fit your brand, from user-experience design to the technical build, ready to grow with you.', 'quantivista' ),
		array( 'WordPress', 'React', 'Next.js' ),
	),
	array(
		'code',
		__( 'Custom software development', 'quantivista' ),
		__( 'Software designed and built around your project\'s actual requirements, with care for business logic, data structure, maintenance and future growth.', 'quantivista' ),
		array( 'C#', 'ASP.NET Core', 'Python' ),
	),
	array(
		'refresh',
		__( 'Process automation and optimisation', 'quantivista' ),
		__( 'Finding repetitive work and designing smart workflows that cut manual effort, bring order to processes and raise productivity.', 'quantivista' ),
		array( 'Workflow Automation', 'API Integration', 'n8n' ),
	),
	array(
		'package',
		__( 'Digital tools and products', 'quantivista' ),
		__( 'Turning specialist ideas into practical software tools, with the focus on user needs, data design and a sound technical structure.', 'quantivista' ),
		array( 'Application Design', 'Database Design', 'API Development' ),
	),
);
?>
<section class="section" aria-labelledby="services-title">
	<div class="container">
		<?php
		qv_section_head(
			array(
				'eyebrow' => __( 'Services', 'quantivista' ),
				'title'   => __( 'From understanding the problem to building the solution', 'quantivista' ),
				'lead'    => __( 'Websites, web applications and custom software, chosen and built for what your project actually needs.', 'quantivista' ),
				'id'      => 'services-title',
			)
		);
		?>
		<div class="grid grid--4 services-grid">
			<?php foreach ( $qv_services as $qv_service ) : ?>
				<article class="service-card" data-reveal>
					<span class="service-card__icon"><?php qv_the_icon( $qv_service[0], array( 'size' => 24 ) ); ?></span>
					<h3 class="service-card__title"><?php echo esc_html( $qv_service[1] ); ?></h3>
					<p class="service-card__text"><?php echo esc_html( $qv_service[2] ); ?></p>
					<ul class="service-card__tech" dir="ltr">
						<?php foreach ( $qv_service[3] as $qv_tech ) : ?>
							<li><?php echo esc_html( $qv_tech ); ?></li>
						<?php endforeach; ?>
					</ul>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
