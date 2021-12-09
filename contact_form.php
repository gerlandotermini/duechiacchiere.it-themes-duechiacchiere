<?php
/*
Template Name: Contact Form
*/

function duechiacchiere_scrub_header( $header ) {
	$headers_to_remove = array(
		'/to\:/i',
		'/from\:/i',
		'/bcc\:/i',
		'/cc\:/i',
		'/content\-transfer\-encoding\:/i',
		'/content\-type\:/i',
		'/mime\-version\:/i' 
	);

 return stripslashes( strip_tags( urldecode( preg_replace( $headers_to_remove, '', $header ) ) ) );
}

$data = array(
	'author' => '',
	'email' => '',
	'message' => ''
);
$content = '';
$error_message = '';

if ( !empty( $_POST[ 'control' ] ) ) {
	foreach( $data as $a_key => $a_value ) {
		if ( empty( $_POST[ $a_key ] ) ) {
			$error_message = "Per favore, prova a metterci un po' pi&ugrave; d'impegno...";
			break;
		}

		$data[ $a_key ] = duechiacchiere_scrub_header( $_POST[ $a_key ] );
	}

	if ( empty( $error_message ) ) {
		$recipient = get_bloginfo( 'admin_email' );
		
		$subject = 'Contatto - ' . get_bloginfo( 'name' );

		$headers = "MIME-Version: 1.0\n";
		$headers .= "From: {$data[ 'author' ]} <{$data[ 'email' ]}>\n";
		$headers .= "Content-Type: text/plain; charset=iso-8859-1\"\n";
		
		$copy_headers = "MIME-Version: 1.0\n";
		$copy_headers .= "From: due chiacchiere <info@duechiacchiere.it>\n";
		$copy_headers .= "Content-Type: text/plain; charset=iso-8859-1\"\n";

		$copy_message = "Grazie per avermi contattato. Il tuo messaggio è stato inviato con successo, e sarà mia cura risponderti nel più breve tempo possibile. Qui di seguito ti allego una copia di quello che hai scritto:\n\n";
		$copy_message .= $data[ 'message' ] . "\n\n";
		$copy_message .= "Il tuo indirizzo IP: " . $_SERVER[ 'REMOTE_ADDR' ] . "\n\n";
		
		$full_message = $data[ 'author' ] . "\n\n";
		$full_message .= $data[ 'message' ] . "\n\n";
		$full_message .= "Indirizzo IP: " . $_SERVER[ 'REMOTE_ADDR' ] . "\n\n";

		if ( class_exists( 'Akismet' ) ) {
			// $Akismet = new Akismet( get_bloginfo( 'url' ),  );

			$akismet_request = array(
				'blog'                  => get_site_url(),
				'user_ip'               => $_SERVER[ 'REMOTE_ADDR' ],
				'user_agent'            => $_SERVER[ 'HTTP_USER_AGENT' ],
				'referrer'              => $_SERVER[ 'HTTP_REFERER' ],
				'comment_type'          => 'comment',
				'comment_author'        => $data[ 'author' ],
				'comment_author_email'  => $data[ 'email' ],
				'comment_content'       => $data[ 'message' ],
				'is_test'               => false,
			);
			$query_string = http_build_query( $akismet_request );
			$response = Akismet::http_post( $query_string, 'comment-check' );

			if ( 'true' != $response[ 1 ] ) {
				// Not spam, let's send the message
				if ( mail( $recipient, $subject, $full_message, $headers ) ) {
					$content = '<p>Grazie per avermi contattato. Il tuo messaggio &egrave; stato inviato con successo, riceverai una mia risposta al pi&ugrave; presto.';
					$content .= "</p><p><strong>Nome</strong>: {$data[ 'author' ]}<br>\n";
					$content .= "<strong>Email</strong>: $recipient</p>\n";
					$content .= "<p><strong>Messaggio</strong>:<br> {$data[ 'message' ]}</p>\n";
				}
				else {
					$error_message = 'Huston, abbiamo un problema. La signora delle pulizie deve aver staccato qualche filo per sbaglio. Per favore, riprova fra qualche minuto, mentre cerchiamo di capire cosa sia successo.';
				}
			}
		}
	}
}

get_header() ?>

<div id="content-wrapper">
	<main id="content">
		<article>
			<header>
				<h1><?php the_title( '', '' ) ?></h1>
			</header>
			<?php if ( !empty( $error_message ) ) echo "<p>Error: $error_message</p>"; ?>

			<?php if ( empty( $content ) ): ?>
				<p>Usa il modulo qui di seguito se vuoi metterti in contatto con me. In genere rispondo in tempi brevi, ma se vedi che ci metto troppo, puoi lasciare un commento a qualche articolo. Chiss&agrave;, magari la mail &egrave; stata mangiata dal filtro antispam.</p>
				<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="comment-form"
					onsubmit="if ( ( this.comment.value == '' ) || ( this.author.value == '' ) || ( this.author.value == this.author.defaultValue ) ) { alert( 'Magari prova a metterci un pochino d\'impegno, che ne dici?' ); return false; }">
					<fieldset>
						<legend class="hidden">Modulo di contatto: nome, email, messaggio</legend>
						<p><label for="author" class="hidden">Nome</label> <input type="text" class="text" name="author" id="author" size="22" maxlength="50" value="<?php echo isset($_COOKIE['comment_author_'.COOKIEHASH])?$_COOKIE['comment_author_'.COOKIEHASH]:''; ?>" /></p>
						<p><label for="email" class="hidden">Email</label> <input type="text" class="text" name="email" id="email" size="22" maxlength="50" value="<?php echo isset($_COOKIE['comment_author_email_'.COOKIEHASH])?$_COOKIE['comment_author_email_'.COOKIEHASH]:''; ?>" /></p>
						<p><label for="message">Messaggio</label> <textarea class="tall" name="message" id="message" cols="65" rows="7"></textarea></p>
						<p><input class="button" type="submit" name="Submit" value="Invia il messaggio" id="contact_submit" /></p>
						<input type="hidden" name="control" value="<?= md5( date( 'His' ) ) ?>" />
					</fieldset>
				</form>
			<?php else: echo $content; endif; ?>
		</article>
	</main>

	<?php get_sidebar() ?>
</div>

<?php get_footer() ?>

<?php /*


include('header.php'); ?>
		<div id="content" class="post-single">
			<h1><?php the_title() ?></h1>
			<?php if (is_user_logged_in()) { echo '<p class="post-information">'; edit_post_link('modifica','',''); echo '</p>'; } ?>
			<?php if (!empty($error)) echo "<p>Error: $error</p>"; 
	if (!$myContactStage){ ?>
			<p>Usa il modulo qui di seguito se vuoi metterti in contatto con me. In genere rispondo in tempi brevi, ma se vedi che ci metto troppo, puoi lasciare un commento a qualche articolo. Chiss&agrave;, magari la mail &egrave; stata mangiata dal filtro antispam.</p>
			<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="comment-form"
				onsubmit="if ( (this.comment.value == '')
						(this.author.value == '') ||
						(this.author.value == this.author.defaultValue) ) { alert('Magari prova a metterci un pochino d\'impegno, che ne dici?'); return false; }">
				<fieldset>
					<legend class="hidden">Modulo di contatto: nome, email, messaggio</legend>
					<p><label for="author" class="hidden">Nome</label> <input type="text" class="text" name="myauthor" id="author" size="22" maxlength="50" value="<?php echo isset($_COOKIE['comment_author_'.COOKIEHASH])?$_COOKIE['comment_author_'.COOKIEHASH]:'nome'; ?>" /></p>
					<p><label for="email" class="hidden">Email</label> <input type="text" class="text" name="myemail" id="email" size="22" maxlength="50" value="<?php echo isset($_COOKIE['comment_author_email_'.COOKIEHASH])?$_COOKIE['comment_author_email_'.COOKIEHASH]:'email'; ?>" /></p>
					<p><label for="comment">Messaggio</label> <textarea class="tall" name="mycomment" id="comment" cols="65" rows="7"></textarea></p>
					<p><input class="button" type="submit" name="Submit" value="Invia il messaggio" id="contact_submit" /></p>
					<input type="hidden" name="mycontact_stage" value="process" />
				</fieldset>
			</form>
<?php 
	}
	else
		echo $myContent;
?>
		</div>
		<!-- END: #content -->
<?php include('sidebar.php') ?>	
	</div>
	<!-- END: #container -->
	
<?php get_footer() */ ?>