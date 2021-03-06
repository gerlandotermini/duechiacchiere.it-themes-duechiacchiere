<?php include_once( 'header.php' ) ?>

<div id="content-wrapper">
	<main id="contenuto">
		<?php /* echo (($wp_query->found_posts==1)?'Trovato ':'Trovati ').$wp_query->found_posts.(($wp_query->found_posts==1)?' risultato':' risultati'); ?> per <strong><?php echo htmlspecialchars(stripslashes($_REQUEST['s'])) ?></strong> */ ?>
		
		<?php
			if ( !is_single() ) {
				echo $heading_title;
			}
		
			if ( !have_posts() ): ?>
				<article>
					<header>
						<h1>Hai trovato la stanza segreta</h1>
					</header>
					
					<p>
						E cos&igrave; ce l'hai fatta: dopo tante fatiche e tante prove, per bravura o per pura fortuna, hai finalmente trovato la
						stanza segreta nascosta nella mia casetta virtuale. Questo &egrave; il tuo punto di partenza da cui accedere ai meandri
						inesplorati di questo sito, per sbirciare sul cosa succede dietro le quinte, quando l'autore pensa di non essere osservato
						da nessuno.
					</p>
					<p>
						<span class="wp-caption aligncenter">
							<img src="<?= get_template_directory_uri() ?>'/img/mappa.jpg'" alt="una mappa planimetrica con tante scritte in inglese">
							<span class="wp-caption-text">L'arcana mappa del sito &egrave; finalmente stata svelata</span>
						</span>
					</p>
					<p>
						Come uno sbarbato Indiana Jones in erba, ora hai il compito di scoprire il significato di quest'illustrazione per
						concludere la tua missione impossibile, e risolvere il mistero che attanaglia tanti esploratori del web da decenni.
						Coloro i quali sapranno trovare la soluzione, avranno accesso ad immense fortune e illimitati poteri sovrumani.
					</p>
					<p>
						&mdash; Buona fortuna.
					</p>

			</article>
			
			<?php
			endif;

			while ( have_posts() ):
				the_post();

				if ( !empty( $_GET[ 'day' ] ) && !empty( $_GET[ 'monthnum' ] ) && get_the_time( 'Y' ) == date_i18n( 'Y' ) ) {
					continue;
				}

				if ( !is_single() ) {
					$categories = get_the_category( $GLOBALS[ 'post' ]->ID );

					// These categories might need to be sorted hierarchically
					usort( $categories, function( $category1, $category2 ) {
						foreach ( get_categories( array( "parent" => $category1->cat_ID ) ) AS $sub ) {
							if ( $category2->cat_ID == $sub->cat_ID ) {
								return -1;
							}
						}
						
						return 1;
					});
				}

				$categories_html = array();
				foreach ( $categories as $a_category ) {
					$categories_html[] = '<a href="' . get_category_link( $a_category->term_id ). '" title="Vai all\'archivio degli articoli per la categoria ' . $a_category->name . '">' . $a_category->name . '</a>';
				}
				$categories_html = implode( ', ', $categories_html );

				$comment_count = get_comments_number();
				switch( $comment_count ) {
					case 0:
						$comments_html = "<a class=\"comments-link\" href=\"" . get_permalink() . "#rispondi\">Lascia un commento</a>";
						break;
					case 1:
						$comments_html = "<span class=\"visually-hidden\">c'&egrave; </span><a class=\"comments-link\" href=\"" . get_permalink() . "#comments\">1 commento</a>";
						break;
					default:
						$comments_html = "<span class=\"visually-hidden\">ci sono </span><a class=\"comments-link\" href=\"" . get_permalink() . "#comments\">{$comment_count} commenti</a>";
				}
				$comments_html .= "<span class=\"visually-hidden\"> per {$GLOBALS[ 'post' ]->post_title}</span>";
		?>
		<article>
			<header>
				<<?= $title_tag ?>><?php if ( !is_single() && !is_page() ): ?><a href="<?php the_permalink() ?>"><?php endif; the_title( '', '' ); if ( !is_single() ): ?></a><?php endif ?></<?= $title_tag ?>>
				<?php if ( $GLOBALS[ 'post' ]->post_type == 'post' ): ?>
				<p class="post-meta">
					<span class="visually-hidden">Scritto il giorno </span><time datetime="<?php the_time( 'Y-m-d H:i:s' ) ?>"><?= ucfirst( get_the_time('l, j F Y') ); ?></time>
					<?php
						if ( !empty( $categories_html ) ) {
							echo '<span class="visually-hidden">Archiviato </span>in ' . $categories_html;
						}
						if ( !empty( $comments_html ) ) {
							echo ' <span class="comment-separator">&mdash;</span> ' . $comments_html;
						}
						if ( !is_single() ) {
							edit_post_link( '[M]', ' ' );
						}
					?>
				</p>
				<?php endif // is post ?>
			</header>
			<div class="entry">
				<?php the_content( '<span class="visually-hidden">' . the_title( '', '', false ) . ': </span>Leggi il resto &raquo;', false ); ?>
			</div>
		</article>

		<?php if ( !post_password_required() && !is_attachment() ) comments_template(); ?>
		<?php endwhile; ?>

		<?php if ( $GLOBALS[ 'wp_query' ]->max_num_pages > 0 ): ?>
		<nav aria-label="Sfoglia le pagine del blog" id="pagination">
			<ul>
				<?php
					$current_page = max( 1, intval( get_query_var( 'paged' ) ) );

					$pages = paginate_links( array(
						'base' => str_replace( 99999, '%#%', esc_url( get_pagenum_link( 99999 ) ) ), 
						'current' => $current_page,
						'format' => '?paged=%#%',
						'prev_text' => 'prev_placeholder',
						'next_text' => 'next_placeholder',
						'total' => $GLOBALS[ 'wp_query' ]->max_num_pages,
						'type'  => 'array'
					) );

					if ( is_array( $pages ) ) {

						if ( $current_page <= 2 ) {
							echo '<li class="pagination-item previous-page"><i></i></li>';
						}
						else {
							echo '<li class="pagination-item previous-page">' . str_replace( array( 'href=', 'prev_placeholder' ), array( "title=\"Vai alla pagina precedente dell'archivio\" href=", '' ), $pages[ 0 ] ) . '</li>';
						}

						foreach ( $pages as $a_page_html ) {
							$loop_page = trim( strip_tags( $a_page_html ) );
							if ( $loop_page != 'prev_placeholder' && $loop_page != 'next_placeholder' ) {
								echo '<li class="pagination-item">' . str_replace( 'href=', "title=\"Vai alla pagina $loop_page dell'archivio\" href=", $a_page_html ) . '</li>';
							}
						}

						if ( $current_page >= $GLOBALS[ 'wp_query' ]->max_num_pages - 1 ) {
							echo '<li class="pagination-item next-page"><i></i></li>';
						}
						else {
							echo '<li class="pagination-item next-page">' . str_replace( array( 'href=', 'next_placeholder' ), array( "title=\"Vai alla pagina successiva dell'archivio\" href=", '' ), $pages[ count( $pages ) - 1 ] ) . '</li>';
						}
					}
				?>
				
			</ul>
		</nav>
		<?php endif ?>
	</main>

	<?php get_sidebar() ?>
</div>

<?php get_footer() ?>