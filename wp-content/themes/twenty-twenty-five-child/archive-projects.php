 <!-- My code for task 5 pagination -->
<?php get_header(); ?>

<div class="projects-archive">
    <h1>Our Projects</h1>

    <?php
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;

    $args = array(
        'post_type'      => 'projects',
        'posts_per_page' => 6,
        'paged'          => $paged,
    );

    $projects_query = new WP_Query($args);

    if ($projects_query->have_posts()) : ?>
        <div class="project-list">
            <?php while ($projects_query->have_posts()) : $projects_query->the_post(); ?>
                <div class="project-item">
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <div><?php the_excerpt(); ?></div>
                </div>
            <?php endwhile; 
            ?>
        </div>

        <div class="pagination">
            <div class="nav-previous"><?php previous_posts_link('« Previous Projects', $projects_query->max_num_pages); ?></div>
            <div class="nav-next"><?php next_posts_link('Next Projects »', $projects_query->max_num_pages); ?></div>
        </div>

        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        <p>No projects found.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
