<?php
/**
 * Placeholder artwork generator (GD).
 *
 * Draws branded UI mockups, abstract blog covers and initials avatars so the
 * seeded site looks finished without stock photography. Replace with real
 * screenshots/photos before launch.
 *
 * @package Quantivista\Scripts
 */

defined( 'ABSPATH' ) || exit;

const QVIMG_NAVY   = '070B24';
const QVIMG_PANEL  = '0F173F';
const QVIMG_CARD   = '162052';
const QVIMG_LINE   = '26306A';
const QVIMG_MUTED  = '7F88B0';
const QVIMG_WHITE  = 'EEF1FF';

function qvimg_font( $bold = false ) {
	$candidates = $bold
		? array( 'C:/Windows/Fonts/segoeuib.ttf', 'C:/Windows/Fonts/arialbd.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', '/Library/Fonts/Arial Bold.ttf' )
		: array( 'C:/Windows/Fonts/segoeui.ttf', 'C:/Windows/Fonts/arial.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf', '/Library/Fonts/Arial.ttf' );
	foreach ( $candidates as $path ) {
		if ( file_exists( $path ) ) {
			return $path;
		}
	}
	return '';
}

function qvimg_rgb( $hex ) {
	$hex = ltrim( $hex, '#' );
	return array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
}

function qvimg_color( $im, $hex, $alpha = 0 ) {
	list( $r, $g, $b ) = qvimg_rgb( $hex );
	return imagecolorallocatealpha( $im, $r, $g, $b, max( 0, min( 127, (int) $alpha ) ) );
}

/**
 * Mix two hex colours (t = 0 → a, 1 → b).
 */
function qvimg_mix( $a, $b, $t ) {
	$ca = qvimg_rgb( $a );
	$cb = qvimg_rgb( $b );
	$out = '';
	for ( $i = 0; $i < 3; $i++ ) {
		$out .= str_pad( dechex( (int) round( $ca[ $i ] + ( $cb[ $i ] - $ca[ $i ] ) * $t ) ), 2, '0', STR_PAD_LEFT );
	}
	return $out;
}

function qvimg_canvas( $w, $h ) {
	$im = imagecreatetruecolor( $w, $h );
	imagealphablending( $im, true );
	imagesavealpha( $im, true );
	return $im;
}

/**
 * Diagonal gradient background.
 */
function qvimg_gradient( $im, $from, $to ) {
	$w = imagesx( $im );
	$h = imagesy( $im );
	imagesetthickness( $im, 2 );
	$total = $w + $h;
	for ( $i = 0; $i <= $total; $i += 2 ) {
		imageline( $im, $i, 0, $i - $h, $h, qvimg_color( $im, qvimg_mix( $from, $to, $i / $total ) ) );
	}
	imagesetthickness( $im, 1 );
}

/**
 * Soft radial glow built from stacked translucent ellipses.
 */
function qvimg_glow( $im, $cx, $cy, $radius, $hex, $strength = 3 ) {
	$steps = 36;
	for ( $i = $steps; $i > 0; $i-- ) {
		$r = (int) ( $radius * $i / $steps );
		imagefilledellipse( $im, $cx, $cy, $r * 2, $r * 2, qvimg_color( $im, $hex, 127 - $strength ) );
	}
}

function qvimg_dot_grid( $im, $gap, $hex, $alpha = 110 ) {
	$c = qvimg_color( $im, $hex, $alpha );
	for ( $y = $gap; $y < imagesy( $im ); $y += $gap ) {
		for ( $x = $gap; $x < imagesx( $im ); $x += $gap ) {
			imagefilledellipse( $im, $x, $y, 3, 3, $c );
		}
	}
}

/**
 * Opaque rounded rectangle (use opaque colours to avoid overlap artefacts).
 */
function qvimg_rrect( $im, $x, $y, $w, $h, $r, $hex ) {
	$c = is_int( $hex ) ? $hex : qvimg_color( $im, $hex );
	$r = (int) min( $r, $w / 2, $h / 2 );
	$x = (int) $x;
	$y = (int) $y;
	$w = (int) $w;
	$h = (int) $h;
	imagealphablending( $im, false );
	imagefilledrectangle( $im, $x + $r, $y, $x + $w - $r, $y + $h, $c );
	imagefilledrectangle( $im, $x, $y + $r, $x + $w, $y + $h - $r, $c );
	imagealphablending( $im, true );
	imagefilledellipse( $im, $x + $r, $y + $r, $r * 2, $r * 2, $c );
	imagefilledellipse( $im, $x + $w - $r, $y + $r, $r * 2, $r * 2, $c );
	imagefilledellipse( $im, $x + $r, $y + $h - $r, $r * 2, $r * 2, $c );
	imagefilledellipse( $im, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $c );
}

/**
 * Rounded rectangle filled with a horizontal gradient.
 */
function qvimg_rrect_gradient( $im, $x, $y, $w, $h, $r, $from, $to ) {
	$layer = qvimg_canvas( $w, $h );
	imagealphablending( $layer, false );
	imagefill( $layer, 0, 0, imagecolorallocatealpha( $layer, 0, 0, 0, 127 ) );
	for ( $i = 0; $i < $w; $i++ ) {
		imageline( $layer, $i, 0, $i, $h, qvimg_color( $layer, qvimg_mix( $from, $to, $i / max( 1, $w ) ) ) );
	}
	// Cut the corners.
	$clear = imagecolorallocatealpha( $layer, 0, 0, 0, 127 );
	for ( $cy = 0; $cy < $r; $cy++ ) {
		for ( $cx = 0; $cx < $r; $cx++ ) {
			if ( ( $r - $cx - 0.5 ) ** 2 + ( $r - $cy - 0.5 ) ** 2 > $r * $r ) {
				imagesetpixel( $layer, $cx, $cy, $clear );
				imagesetpixel( $layer, $w - 1 - $cx, $cy, $clear );
				imagesetpixel( $layer, $cx, $h - 1 - $cy, $clear );
				imagesetpixel( $layer, $w - 1 - $cx, $h - 1 - $cy, $clear );
			}
		}
	}
	imagecopy( $im, $layer, (int) $x, (int) $y, 0, 0, $w, $h );
	imagedestroy( $layer );
}

function qvimg_text( $im, $size, $x, $y, $hex, $text, $bold = false, $alpha = 0 ) {
	$font = qvimg_font( $bold );
	if ( $font ) {
		imagettftext( $im, $size, 0, (int) $x, (int) $y, qvimg_color( $im, $hex, $alpha ), $font, $text );
	} else {
		imagestring( $im, 5, (int) $x, (int) $y - 14, $text, qvimg_color( $im, $hex, $alpha ) );
	}
}

function qvimg_text_width( $size, $text, $bold = false ) {
	$font = qvimg_font( $bold );
	if ( ! $font ) {
		return strlen( $text ) * 9;
	}
	$box = imagettfbbox( $size, 0, $font, $text );
	return abs( $box[2] - $box[0] );
}

/**
 * Placeholder text line (pill).
 */
function qvimg_line( $im, $x, $y, $w, $h, $hex ) {
	qvimg_rrect( $im, $x, $y, $w, $h, (int) ( $h / 2 ), $hex );
}

function qvimg_polyline( $im, array $points, $hex, $thickness = 4 ) {
	imagesetthickness( $im, $thickness );
	$c = qvimg_color( $im, $hex );
	for ( $i = 1, $n = count( $points ); $i < $n; $i++ ) {
		imageline( $im, (int) $points[ $i - 1 ][0], (int) $points[ $i - 1 ][1], (int) $points[ $i ][0], (int) $points[ $i ][1], $c );
	}
	imagesetthickness( $im, 1 );
	foreach ( array( reset( $points ), end( $points ) ) as $p ) {
		imagefilledellipse( $im, (int) $p[0], (int) $p[1], $thickness + 2, $thickness + 2, $c );
	}
}

/**
 * Deterministic pseudo-random series for charts.
 */
function qvimg_series( $seed, $count, $min = 0.2, $max = 1.0 ) {
	mt_srand( crc32( (string) $seed ) );
	$out  = array();
	$prev = ( $min + $max ) / 2;
	for ( $i = 0; $i < $count; $i++ ) {
		$prev  = max( $min, min( $max, $prev + ( mt_rand( -100, 130 ) / 100 ) * 0.18 ) );
		$out[] = $prev;
	}
	mt_srand();
	return $out;
}

function qvimg_background( $im, $accent, $accent2 ) {
	$w = imagesx( $im );
	$h = imagesy( $im );
	qvimg_gradient( $im, '090E2C', '141C4E' );
	qvimg_glow( $im, (int) ( $w * 0.92 ), (int) ( $h * 0.05 ), (int) ( $w * 0.45 ), $accent2, 3 );
	qvimg_glow( $im, (int) ( $w * 0.05 ), (int) ( $h * 1.0 ), (int) ( $w * 0.4 ), $accent, 3 );
	qvimg_dot_grid( $im, 40, 'FFFFFF', 118 );
}

/**
 * Browser/app window chrome. Returns the content box [x, y, w, h].
 */
function qvimg_window( $im, $x, $y, $w, $h, $label, $light = false ) {
	qvimg_rrect( $im, $x - 2, $y - 2, $w + 4, $h + 4, 24, $light ? 'C9D0E6' : QVIMG_LINE );
	qvimg_rrect( $im, $x, $y, $w, $h, 22, $light ? 'FFFFFF' : QVIMG_PANEL );
	$bar = $light ? 'EEF1F8' : '0B1234';
	qvimg_rrect( $im, $x, $y, $w, 64, 22, $bar );
	imagefilledrectangle( $im, $x, $y + 40, $x + $w, $y + 64, qvimg_color( $im, $bar ) );
	foreach ( array( 'F87171', 'FBBF24', '34D399' ) as $i => $dot ) {
		imagefilledellipse( $im, $x + 34 + $i * 26, $y + 32, 14, 14, qvimg_color( $im, $dot, 30 ) );
	}
	$pill_w = min( 520, (int) ( $w * 0.42 ) );
	qvimg_rrect( $im, $x + ( $w - $pill_w ) / 2, $y + 16, $pill_w, 32, 16, $light ? 'FFFFFF' : '141C48' );
	qvimg_text( $im, 13, $x + ( $w - $pill_w ) / 2 + 22, $y + 38, $light ? '6B7391' : QVIMG_MUTED, $label );
	return array( $x, $y + 64, $w, $h - 64 );
}

/* ------------------------------------------------------------------------
 * Mockups
 * --------------------------------------------------------------------- */

function qvimg_mock_dashboard( $w, $h, $title, $accent, $accent2, $seed ) {
	$im = qvimg_canvas( $w, $h );
	qvimg_background( $im, $accent, $accent2 );

	list( $x, $y, $cw, $ch ) = qvimg_window( $im, (int) ( $w * 0.08 ), (int) ( $h * 0.1 ), (int) ( $w * 0.84 ), (int) ( $h * 0.84 ), strtolower( str_replace( ' ', '', $title ) ) . '.app/dashboard' );

	// Sidebar.
	$sb = (int) ( $cw * 0.18 );
	imagefilledrectangle( $im, $x, $y, $x + $sb, $y + $ch - 22, qvimg_color( $im, '0C1336' ) );
	qvimg_rrect_gradient( $im, $x + 28, $y + 28, 40, 40, 10, $accent, $accent2 );
	qvimg_text( $im, 17, $x + 80, $y + 55, QVIMG_WHITE, $title, true );
	for ( $i = 0; $i < 7; $i++ ) {
		$iy = $y + 110 + $i * 52;
		if ( 1 === $i ) {
			qvimg_rrect( $im, $x + 18, $iy - 12, $sb - 36, 42, 10, qvimg_mix( '0C1336', $accent, 0.22 ) );
		}
		qvimg_rrect( $im, $x + 34, $iy, 18, 18, 5, 1 === $i ? $accent : '2A3470' );
		qvimg_line( $im, $x + 64, $iy + 4, (int) ( $sb * ( 0.35 + ( ( $i * 37 ) % 30 ) / 100 ) ), 10, 1 === $i ? 'C7D2FF' : '2A3470' );
	}

	// Header.
	$mx = $x + $sb + 40;
	$mw = $cw - $sb - 80;
	qvimg_text( $im, 30, $mx, $y + 70, QVIMG_WHITE, 'Overview', true );
	qvimg_line( $im, $mx, $y + 90, 260, 12, '2A3470' );
	qvimg_rrect_gradient( $im, $mx + $mw - 170, $y + 40, 170, 46, 12, $accent, $accent2 );
	qvimg_text( $im, 15, $mx + $mw - 140, $y + 70, QVIMG_NAVY, '+ New report', true );

	// KPI cards.
	$kpis   = array( array( 'Revenue', '$48.2k', '+12.4%' ), array( 'Active users', '12,840', '+8.1%' ), array( 'Conversion', '4.62%', '+0.9%' ), array( 'Churn', '1.8%', '-0.4%' ) );
	$gap    = 22;
	$kw     = (int) ( ( $mw - $gap * 3 ) / 4 );
	$ky     = $y + 130;
	foreach ( $kpis as $i => $kpi ) {
		$kx = $mx + $i * ( $kw + $gap );
		qvimg_rrect( $im, $kx, $ky, $kw, 140, 16, QVIMG_CARD );
		qvimg_text( $im, 13, $kx + 22, $ky + 38, QVIMG_MUTED, $kpi[0] );
		qvimg_text( $im, 26, $kx + 22, $ky + 84, QVIMG_WHITE, $kpi[1], true );
		qvimg_text( $im, 12, $kx + 22, $ky + 116, '34D399', $kpi[2], true );
		$spark = qvimg_series( $seed . $i, 8 );
		$pts   = array();
		foreach ( $spark as $j => $v ) {
			$pts[] = array( $kx + $kw - 120 + $j * 14, $ky + 120 - $v * 44 );
		}
		qvimg_polyline( $im, $pts, 0 === $i % 2 ? $accent : $accent2, 3 );
	}

	// Chart card.
	$cy2 = $ky + 170;
	$chw = (int) ( $mw * 0.64 );
	$chh = $y + $ch - 50 - $cy2;
	qvimg_rrect( $im, $mx, $cy2, $chw, $chh, 16, QVIMG_CARD );
	qvimg_text( $im, 16, $mx + 24, $cy2 + 40, QVIMG_WHITE, 'Performance', true );
	qvimg_line( $im, $mx + 24, $cy2 + 56, 140, 9, '2A3470' );
	$bars = qvimg_series( $seed . 'bars', 12, 0.25, 1 );
	$bw   = (int) ( ( $chw - 60 ) / 12 );
	$base = $cy2 + $chh - 30;
	$area = $chh - 110;
	for ( $i = 0; $i < 12; $i++ ) {
		imageline( $im, $mx + 24, (int) ( $base - $area * $i / 4 ), $mx + $chw - 24, (int) ( $base - $area * $i / 4 ), qvimg_color( $im, '1F2A63' ) );
		if ( $i > 4 ) {
			continue;
		}
	}
	for ( $i = 0; $i < 12; $i++ ) {
		$bh = (int) ( $area * $bars[ $i ] * 0.8 );
		qvimg_rrect_gradient( $im, $mx + 34 + $i * $bw, $base - $bh, (int) ( $bw * 0.55 ), $bh, 6, qvimg_mix( $accent, '162052', 0.15 ), $accent2 );
	}
	$line = qvimg_series( $seed . 'line', 12, 0.35, 0.95 );
	$pts  = array();
	foreach ( $line as $i => $v ) {
		$pts[] = array( $mx + 34 + $i * $bw + $bw * 0.27, $base - $area * $v );
	}
	qvimg_polyline( $im, $pts, 'FFFFFF', 3 );

	// Activity list.
	$lx = $mx + $chw + $gap;
	$lw = $mw - $chw - $gap;
	qvimg_rrect( $im, $lx, $cy2, $lw, $chh, 16, QVIMG_CARD );
	qvimg_text( $im, 16, $lx + 24, $cy2 + 40, QVIMG_WHITE, 'Recent activity', true );
	$palette = array( $accent, $accent2, '93C5FD', '2DD4BF', 'A5B4FC' );
	for ( $i = 0; $i < 5; $i++ ) {
		$ry = $cy2 + 80 + $i * (int) ( ( $chh - 100 ) / 5 );
		imagefilledellipse( $im, $lx + 44, $ry + 16, 36, 36, qvimg_color( $im, $palette[ $i ] ) );
		qvimg_line( $im, $lx + 76, $ry + 4, (int) ( $lw * 0.45 ), 10, '3A4585' );
		qvimg_line( $im, $lx + 76, $ry + 22, (int) ( $lw * 0.3 ), 8, '252F66' );
	}

	return $im;
}

function qvimg_mock_kanban( $w, $h, $title, $accent, $accent2, $seed ) {
	$im = qvimg_canvas( $w, $h );
	qvimg_background( $im, $accent2, $accent );
	list( $x, $y, $cw, $ch ) = qvimg_window( $im, (int) ( $w * 0.08 ), (int) ( $h * 0.1 ), (int) ( $w * 0.84 ), (int) ( $h * 0.84 ), strtolower( str_replace( ' ', '', $title ) ) . '.app/pipeline' );

	qvimg_text( $im, 28, $x + 40, $y + 66, QVIMG_WHITE, 'Pipeline', true );
	qvimg_text( $im, 14, $x + 42, $y + 96, QVIMG_MUTED, $title . ' · 128 open deals · $1.24M weighted' );
	foreach ( array( 'All', 'Mine', 'This quarter' ) as $i => $tab ) {
		$tw = qvimg_text_width( 13, $tab, true ) + 34;
		$tx = $x + $cw - 420 + $i * 130;
		qvimg_rrect( $im, $tx, $y + 44, $tw, 36, 18, 0 === $i ? qvimg_mix( QVIMG_PANEL, $accent, 0.35 ) : QVIMG_CARD );
		qvimg_text( $im, 13, $tx + 17, $y + 68, QVIMG_WHITE, $tab, true );
	}

	$cols  = array( 'Lead', 'Qualified', 'Proposal', 'Won' );
	$dots  = array( '94A3B8', $accent, '60A5FA', '34D399' );
	$gap   = 22;
	$colw  = (int) ( ( $cw - 80 - $gap * 3 ) / 4 );
	$top   = $y + 130;
	mt_srand( crc32( $seed ) );
	foreach ( $cols as $i => $col ) {
		$cx = $x + 40 + $i * ( $colw + $gap );
		qvimg_rrect( $im, $cx, $top, $colw, $ch - 170, 16, '0C1336' );
		imagefilledellipse( $im, $cx + 26, $top + 30, 12, 12, qvimg_color( $im, $dots[ $i ] ) );
		qvimg_text( $im, 15, $cx + 42, $top + 36, QVIMG_WHITE, $col, true );
		qvimg_text( $im, 13, $cx + $colw - 40, $top + 36, QVIMG_MUTED, (string) mt_rand( 4, 24 ) );
		$cards = 3 - ( $i % 2 );
		for ( $c = 0; $c < $cards + 1; $c++ ) {
			$cardy = $top + 64 + $c * 150;
			if ( $cardy + 130 > $top + $ch - 190 ) {
				break;
			}
			qvimg_rrect( $im, $cx + 14, $cardy, $colw - 28, 130, 12, QVIMG_CARD );
			qvimg_line( $im, $cx + 34, $cardy + 26, (int) ( $colw * 0.55 ), 11, '5B66A8' );
			qvimg_line( $im, $cx + 34, $cardy + 50, (int) ( $colw * 0.38 ), 9, '2F3A78' );
			qvimg_rrect( $im, $cx + 34, $cardy + 80, 74, 26, 13, qvimg_mix( QVIMG_CARD, $dots[ $i ], 0.35 ) );
			qvimg_text( $im, 12, $cx + 48, $cardy + 98, QVIMG_WHITE, '$' . mt_rand( 2, 48 ) . 'k', true );
			imagefilledellipse( $im, $cx + $colw - 50, $cardy + 93, 30, 30, qvimg_color( $im, 0 === $c % 2 ? $accent2 : $accent ) );
		}
	}
	mt_srand();
	return $im;
}

function qvimg_mock_flow( $w, $h, $title, $accent, $accent2, $seed ) {
	$im = qvimg_canvas( $w, $h );
	qvimg_background( $im, $accent, $accent2 );
	list( $x, $y, $cw, $ch ) = qvimg_window( $im, (int) ( $w * 0.08 ), (int) ( $h * 0.1 ), (int) ( $w * 0.84 ), (int) ( $h * 0.84 ), strtolower( str_replace( ' ', '', $title ) ) . '.app/workflows' );
	qvimg_dot_grid( $im, 28, '3A4585', 90 );

	qvimg_text( $im, 26, $x + 40, $y + 62, QVIMG_WHITE, 'New order → Fulfilment', true );
	qvimg_rrect( $im, $x + 40, $y + 82, 110, 30, 15, qvimg_mix( QVIMG_PANEL, '34D399', 0.3 ) );
	qvimg_text( $im, 12, $x + 58, $y + 102, 'A7F3D0', '● Active', true );

	$nodes = array(
		array( 0.10, 0.30, 'Trigger', 'Order created', $accent ),
		array( 0.38, 0.18, 'Condition', 'Total > $500', 'F59E0B' ),
		array( 0.38, 0.55, 'Action', 'Send to Slack', $accent2 ),
		array( 0.66, 0.18, 'Action', 'Create invoice', $accent2 ),
		array( 0.66, 0.55, 'Delay', 'Wait 2 hours', '94A3B8' ),
		array( 0.66, 0.85, 'Action', 'Email customer', $accent ),
	);
	$links = array( array( 0, 1 ), array( 0, 2 ), array( 1, 3 ), array( 2, 4 ), array( 4, 5 ) );
	$nw    = (int) ( $cw * 0.22 );
	$nh    = 96;
	$pos   = array();
	foreach ( $nodes as $i => $n ) {
		$pos[ $i ] = array( (int) ( $x + $cw * $n[0] ), (int) ( $y + 90 + ( $ch - 200 ) * $n[1] ) );
	}
	imagesetthickness( $im, 3 );
	foreach ( $links as $l ) {
		$a  = $pos[ $l[0] ];
		$b  = $pos[ $l[1] ];
		$x1 = $a[0] + $nw;
		$y1 = $a[1] + $nh / 2;
		$x2 = $b[0];
		$y2 = $b[1] + $nh / 2;
		$prev = array( $x1, $y1 );
		for ( $t = 1; $t <= 24; $t++ ) {
			$tt = $t / 24;
			$px = ( 1 - $tt ) ** 3 * $x1 + 3 * ( 1 - $tt ) ** 2 * $tt * ( $x1 + 80 ) + 3 * ( 1 - $tt ) * $tt ** 2 * ( $x2 - 80 ) + $tt ** 3 * $x2;
			$py = ( 1 - $tt ) ** 3 * $y1 + 3 * ( 1 - $tt ) ** 2 * $tt * $y1 + 3 * ( 1 - $tt ) * $tt ** 2 * $y2 + $tt ** 3 * $y2;
			imageline( $im, (int) $prev[0], (int) $prev[1], (int) $px, (int) $py, qvimg_color( $im, '4B57A0' ) );
			$prev = array( $px, $py );
		}
		imagefilledellipse( $im, (int) $x2, (int) $y2, 12, 12, qvimg_color( $im, $accent ) );
	}
	imagesetthickness( $im, 1 );
	foreach ( $nodes as $i => $n ) {
		list( $nx, $ny ) = $pos[ $i ];
		qvimg_rrect( $im, $nx - 2, $ny - 2, $nw + 4, $nh + 4, 16, qvimg_mix( QVIMG_CARD, $n[4], 0.45 ) );
		qvimg_rrect( $im, $nx, $ny, $nw, $nh, 14, QVIMG_CARD );
		qvimg_rrect( $im, $nx + 18, $ny + 22, 52, 52, 12, qvimg_mix( QVIMG_CARD, $n[4], 0.4 ) );
		imagefilledellipse( $im, $nx + 44, $ny + 48, 18, 18, qvimg_color( $im, $n[4] ) );
		qvimg_text( $im, 12, $nx + 86, $ny + 40, QVIMG_MUTED, strtoupper( $n[2] ), true );
		qvimg_text( $im, 16, $nx + 86, $ny + 66, QVIMG_WHITE, $n[3], true );
	}
	return $im;
}

function qvimg_phone( $im, $x, $y, $pw, $ph, $accent, $accent2, $variant, $seed ) {
	qvimg_rrect( $im, $x - 10, $y - 10, $pw + 20, $ph + 20, 58, '03050F' );
	qvimg_rrect( $im, $x - 12, $y - 12, $pw + 24, $ph + 24, 60, '2A3470' );
	qvimg_rrect( $im, $x - 10, $y - 10, $pw + 20, $ph + 20, 58, '03050F' );
	$light = 1 === $variant % 2;
	qvimg_rrect( $im, $x, $y, $pw, $ph, 48, $light ? 'F5F7FC' : '0D1440' );
	$ink   = $light ? '0B1026' : QVIMG_WHITE;
	$line  = $light ? 'D6DCEB' : '2A3470';
	$card  = $light ? 'FFFFFF' : '162052';
	qvimg_rrect( $im, $x + $pw / 2 - 56, $y + 14, 112, 30, 15, '03050F' );
	qvimg_text( $im, 13, $x + 32, $y + 36, $ink, '9:41', true );

	$pad = 26;
	qvimg_text( $im, 13, $x + $pad, $y + 92, $light ? '6B7391' : QVIMG_MUTED, 0 === $variant ? 'Good morning' : 'Today' );
	qvimg_text( $im, 24, $x + $pad, $y + 126, $ink, 0 === $variant ? 'Your progress' : ( 1 === $variant ? 'Schedule' : 'Deliveries' ), true );
	qvimg_rrect_gradient( $im, $x + $pad, $y + 150, $pw - $pad * 2, 170, 24, $accent, $accent2 );
	qvimg_text( $im, 14, $x + $pad + 22, $y + 190, '070B24', 0 === $variant ? 'Weekly goal' : 'Next up', true );
	qvimg_text( $im, 34, $x + $pad + 22, $y + 240, '070B24', 0 === $variant ? '78%' : '10:30', true );
	qvimg_rrect( $im, $x + $pad + 22, $y + 270, $pw - $pad * 2 - 44, 12, 6, qvimg_mix( $accent, '070B24', 0.25 ) );
	qvimg_rrect( $im, $x + $pad + 22, $y + 270, (int) ( ( $pw - $pad * 2 - 44 ) * 0.78 ), 12, 6, '070B24' );

	$rows = 4;
	for ( $i = 0; $i < $rows; $i++ ) {
		$ry = $y + 350 + $i * 92;
		if ( $ry + 76 > $y + $ph - 110 ) {
			break;
		}
		qvimg_rrect( $im, $x + $pad, $ry, $pw - $pad * 2, 76, 18, $card );
		qvimg_rrect( $im, $x + $pad + 14, $ry + 14, 48, 48, 14, qvimg_mix( $card, 0 === $i % 2 ? $accent : $accent2, 0.35 ) );
		qvimg_line( $im, $x + $pad + 76, $ry + 22, (int) ( $pw * 0.38 ), 11, $line );
		qvimg_line( $im, $x + $pad + 76, $ry + 44, (int) ( $pw * 0.24 ), 9, $light ? 'E5E9F3' : '222C62' );
	}
	qvimg_rrect( $im, $x + 18, $y + $ph - 92, $pw - 36, 70, 26, $card );
	for ( $i = 0; $i < 4; $i++ ) {
		$ix = (int) ( $x + 18 + ( $pw - 36 ) * ( $i + 0.5 ) / 4 );
		imagefilledellipse( $im, $ix, $y + $ph - 57, 22, 22, qvimg_color( $im, 0 === $i ? $accent : $line ) );
	}
}

function qvimg_mock_phones( $w, $h, $title, $accent, $accent2, $seed ) {
	$im = qvimg_canvas( $w, $h );
	qvimg_background( $im, $accent, $accent2 );
	$ph = (int) ( $h * 0.84 );
	$pw = (int) ( $ph * 0.49 );
	$cx = (int) ( $w / 2 );
	qvimg_phone( $im, $cx - $pw - (int) ( $pw * 0.45 ), (int) ( $h * 0.16 ), (int) ( $pw * 0.9 ), (int) ( $ph * 0.9 ), $accent2, $accent, 1, $seed );
	qvimg_phone( $im, $cx + (int) ( $pw * 0.55 ), (int) ( $h * 0.16 ), (int) ( $pw * 0.9 ), (int) ( $ph * 0.9 ), $accent, $accent2, 2, $seed );
	qvimg_phone( $im, $cx - (int) ( $pw / 2 ), (int) ( $h * 0.08 ), $pw, $ph, $accent, $accent2, 0, $seed );
	qvimg_text( $im, 18, 60, $h - 50, QVIMG_WHITE, $title, true, 20 );
	return $im;
}

function qvimg_mock_landing( $w, $h, $title, $accent, $accent2, $seed ) {
	$im = qvimg_canvas( $w, $h );
	qvimg_background( $im, $accent, $accent2 );
	list( $x, $y, $cw, $ch ) = qvimg_window( $im, (int) ( $w * 0.08 ), (int) ( $h * 0.1 ), (int) ( $w * 0.84 ), (int) ( $h * 0.84 ), strtolower( str_replace( ' ', '', $title ) ) . '.com', true );

	$ink = '0B1026';
	qvimg_rrect_gradient( $im, $x + 40, $y + 28, 34, 34, 9, $accent, $accent2 );
	qvimg_text( $im, 17, $x + 86, $y + 53, $ink, $title, true );
	foreach ( array( 90, 70, 96, 60 ) as $i => $lw ) {
		qvimg_line( $im, $x + $cw / 2 - 170 + $i * 110, $y + 40, $lw, 10, 'D6DCEB' );
	}
	qvimg_rrect( $im, $x + $cw - 170, $y + 26, 130, 40, 12, '070B24' );
	qvimg_text( $im, 13, $x + $cw - 142, $y + 51, 'FFFFFF', 'Get started', true );

	$hx = $x + 60;
	$hy = $y + 150;
	qvimg_rrect( $im, $hx, $hy, 200, 32, 16, qvimg_mix( 'FFFFFF', $accent, 0.18 ) );
	qvimg_text( $im, 12, $hx + 18, $hy + 21, qvimg_mix( $accent, '0B1026', 0.45 ), 'NEW · Launch faster', true );
	qvimg_text( $im, 44, $hx, $hy + 100, $ink, 'Design that', true );
	qvimg_text( $im, 44, $hx, $hy + 158, $ink, 'converts.', true );
	qvimg_line( $im, $hx, $hy + 196, (int) ( $cw * 0.36 ), 12, 'D6DCEB' );
	qvimg_line( $im, $hx, $hy + 222, (int) ( $cw * 0.28 ), 12, 'D6DCEB' );
	qvimg_rrect_gradient( $im, $hx, $hy + 262, 180, 54, 14, $accent, $accent2 );
	qvimg_text( $im, 15, $hx + 36, $hy + 296, '070B24', 'Book a call', true );
	qvimg_rrect( $im, $hx + 196, $hy + 262, 150, 54, 14, 'EEF1F8' );
	qvimg_text( $im, 15, $hx + 226, $hy + 296, $ink, 'See work', true );

	$ix = $x + (int) ( $cw * 0.52 );
	$iw = $cw - ( $ix - $x ) - 60;
	qvimg_rrect_gradient( $im, $ix, $hy - 10, $iw, 350, 24, qvimg_mix( $accent, 'FFFFFF', 0.1 ), $accent2 );
	imagefilledellipse( $im, $ix + (int) ( $iw * 0.7 ), $hy + 110, 220, 220, qvimg_color( $im, 'FFFFFF', 95 ) );
	qvimg_rrect( $im, $ix + 40, $hy + 170, (int) ( $iw * 0.55 ), 140, 16, 'FFFFFF' );
	qvimg_line( $im, $ix + 64, $hy + 200, (int) ( $iw * 0.3 ), 12, 'C9D0E6' );
	qvimg_line( $im, $ix + 64, $hy + 226, (int) ( $iw * 0.4 ), 10, 'E5E9F3' );
	$bars = qvimg_series( $seed, 6, 0.3, 1 );
	foreach ( $bars as $i => $v ) {
		qvimg_rrect( $im, $ix + 64 + $i * 36, $hy + 290 - (int) ( 40 * $v ), 22, (int) ( 40 * $v ), 4, 0 === $i % 2 ? $accent : $accent2 );
	}

	$fy = $y + $ch - 200;
	$fw = (int) ( ( $cw - 120 - 48 ) / 3 );
	for ( $i = 0; $i < 3; $i++ ) {
		$fx = $x + 60 + $i * ( $fw + 24 );
		qvimg_rrect( $im, $fx - 1, $fy - 1, $fw + 2, 162, 17, 'E3E7F1' );
		qvimg_rrect( $im, $fx, $fy, $fw, 160, 16, 'FAFBFE' );
		qvimg_rrect( $im, $fx + 24, $fy + 24, 44, 44, 12, qvimg_mix( 'FFFFFF', 0 === $i % 2 ? $accent : $accent2, 0.25 ) );
		qvimg_line( $im, $fx + 24, $fy + 92, (int) ( $fw * 0.5 ), 12, 'C9D0E6' );
		qvimg_line( $im, $fx + 24, $fy + 118, (int) ( $fw * 0.75 ), 10, 'E5E9F3' );
	}
	return $im;
}

function qvimg_mock_pages( $w, $h, $title, $accent, $accent2, $seed ) {
	$im = qvimg_canvas( $w, $h );
	qvimg_background( $im, $accent2, $accent );
	$pw = (int) ( $w * 0.26 );
	$ph = (int) ( $h * 0.7 );
	foreach ( array( -1, 1, 0 ) as $k => $offset ) {
		$px = (int) ( $w / 2 - $pw / 2 + $offset * $pw * 0.95 );
		$py = (int) ( $h * 0.15 + abs( $offset ) * 40 );
		qvimg_rrect( $im, $px - 2, $py - 2, $pw + 4, $ph + 4, 20, 'C9D0E6' );
		qvimg_rrect( $im, $px, $py, $pw, $ph, 18, 'FFFFFF' );
		qvimg_rrect_gradient( $im, $px + 18, $py + 18, $pw - 36, (int) ( $ph * 0.36 ), 12, 0 === $k ? $accent2 : $accent, 1 === $k ? $accent : $accent2 );
		qvimg_line( $im, $px + 18, $py + (int) ( $ph * 0.43 ), (int) ( $pw * 0.6 ), 14, '0B1026' );
		qvimg_line( $im, $px + 18, $py + (int) ( $ph * 0.43 ) + 30, (int) ( $pw * 0.75 ), 10, 'D6DCEB' );
		qvimg_line( $im, $px + 18, $py + (int) ( $ph * 0.43 ) + 50, (int) ( $pw * 0.5 ), 10, 'D6DCEB' );
		for ( $i = 0; $i < 2; $i++ ) {
			qvimg_rrect( $im, $px + 18 + $i * (int) ( ( $pw - 48 ) / 2 + 12 ), $py + (int) ( $ph * 0.62 ), (int) ( ( $pw - 48 ) / 2 ), (int) ( $ph * 0.28 ), 10, 'F1F4FA' );
		}
	}
	qvimg_text( $im, 18, 60, $h - 50, QVIMG_WHITE, $title, true, 20 );
	return $im;
}

/**
 * Abstract cover for blog posts.
 */
function qvimg_cover( $w, $h, $glyph, $accent, $accent2, $seed ) {
	$im = qvimg_canvas( $w, $h );
	qvimg_gradient( $im, '070B24', qvimg_mix( '18225A', $accent2, 0.25 ) );
	qvimg_glow( $im, (int) ( $w * 0.78 ), (int) ( $h * 0.3 ), (int) ( $w * 0.42 ), $accent, 4 );
	qvimg_glow( $im, (int) ( $w * 0.15 ), (int) ( $h * 0.95 ), (int) ( $w * 0.35 ), $accent2, 4 );
	qvimg_dot_grid( $im, 36, 'FFFFFF', 115 );

	mt_srand( crc32( $seed ) );
	imagesetthickness( $im, 2 );
	for ( $i = 0; $i < 5; $i++ ) {
		$r = mt_rand( 120, 420 );
		imageellipse( $im, mt_rand( 0, $w ), mt_rand( 0, $h ), $r * 2, $r * 2, qvimg_color( $im, 'FFFFFF', 112 ) );
	}
	imagesetthickness( $im, 1 );
	mt_srand();

	$size = (int) ( $h * 0.3 );
	$tw   = qvimg_text_width( $size, $glyph, true );
	qvimg_rrect( $im, (int) ( $w / 2 - $tw / 2 - 70 ), (int) ( $h / 2 - $size * 0.9 ), $tw + 140, (int) ( $size * 1.6 ), 48, qvimg_mix( '0D1440', $accent, 0.12 ) );
	qvimg_text( $im, $size, $w / 2 - $tw / 2, $h / 2 + $size * 0.45, qvimg_mix( $accent, 'FFFFFF', 0.35 ), $glyph, true );
	return $im;
}

/**
 * Shared backdrop for article illustrations (same look as qvimg_cover).
 */
function qvimg_cover_bg( $w, $h, $accent, $accent2 ) {
	$im = qvimg_canvas( $w, $h );
	qvimg_gradient( $im, '070B24', qvimg_mix( '18225A', $accent2, 0.25 ) );
	qvimg_glow( $im, (int) ( $w * 0.8 ), (int) ( $h * 0.25 ), (int) ( $w * 0.4 ), $accent, 4 );
	qvimg_glow( $im, (int) ( $w * 0.12 ), (int) ( $h * 0.95 ), (int) ( $w * 0.35 ), $accent2, 4 );
	qvimg_dot_grid( $im, 36, 'FFFFFF', 115 );
	return $im;
}

/**
 * Arrow from (x1,y1) to (x2,y2) with a filled head.
 */
function qvimg_arrow( $im, $x1, $y1, $x2, $y2, $hex, $thickness = 6 ) {
	imagesetthickness( $im, $thickness );
	imageline( $im, (int) $x1, (int) $y1, (int) $x2, (int) $y2, qvimg_color( $im, $hex ) );
	imagesetthickness( $im, 1 );
	$a  = atan2( $y2 - $y1, $x2 - $x1 );
	$hl = $thickness * 4;
	imagefilledpolygon(
		$im,
		array(
			(int) $x2, (int) $y2,
			(int) ( $x2 - $hl * cos( $a - 0.45 ) ), (int) ( $y2 - $hl * sin( $a - 0.45 ) ),
			(int) ( $x2 - $hl * cos( $a + 0.45 ) ), (int) ( $y2 - $hl * sin( $a + 0.45 ) ),
		),
		qvimg_color( $im, $hex )
	);
}

/**
 * Article: booking calendar where a second, clashing booking is blocked.
 */
function qvimg_cover_booking( $w, $h, $accent, $accent2 ) {
	$im    = qvimg_cover_bg( $w, $h, $accent, $accent2 );
	$panel = '0D1440';
	$cx    = (int) ( $w * 0.2 );
	$cy    = (int) ( $h * 0.16 );
	$cw    = (int) ( $w * 0.6 );
	$ch    = (int) ( $h * 0.68 );
	qvimg_rrect( $im, $cx, $cy, $cw, $ch, 36, $panel );
	qvimg_rrect( $im, $cx, $cy, $cw, 96, 36, qvimg_mix( $panel, $accent2, 0.35 ) );
	qvimg_rrect( $im, $cx, $cy + 60, $cw, 36, 0, qvimg_mix( $panel, $accent2, 0.35 ) );
	foreach ( array( 0.22, 0.78 ) as $px ) { // Binder rings.
		qvimg_rrect( $im, $cx + $cw * $px - 10, $cy - 26, 20, 56, 10, 'C7D2FE' );
	}
	$cols = 5;
	$rows = 4;
	$gx   = $cx + 40;
	$gy   = $cy + 130;
	$cellw = ( $cw - 80 ) / $cols;
	$cellh = ( $ch - 170 ) / $rows;
	for ( $r = 0; $r < $rows; $r++ ) {
		for ( $c = 0; $c < $cols; $c++ ) {
			qvimg_rrect( $im, $gx + $c * $cellw + 6, $gy + $r * $cellh + 6, $cellw - 12, $cellh - 12, 14, qvimg_mix( $panel, 'FFFFFF', 0.06 ) );
		}
	}
	// Confirmed booking (cyan) in one slot.
	$bx = $gx + 2 * $cellw + 6;
	$by = $gy + 1 * $cellh + 6;
	qvimg_rrect_gradient( $im, $bx, $by, (int) ( $cellw - 12 ), (int) ( $cellh - 12 ), 14, $accent, qvimg_mix( $accent, $accent2, 0.5 ) );
	qvimg_line( $im, $bx + 18, $by + 22, $cellw * 0.5, 12, 'FFFFFF' );
	qvimg_line( $im, $bx + 18, $by + 46, $cellw * 0.3, 10, 'E0F2FE' );
	// Second booking for the same slot, offset and stopped by a shield.
	$ox = $bx + $cellw * 0.55;
	$oy = $by + $cellh * 0.55;
	qvimg_rrect( $im, $ox, $oy, (int) ( $cellw - 12 ), (int) ( $cellh - 12 ), 14, qvimg_mix( $panel, $accent2, 0.55 ) );
	qvimg_line( $im, $ox + 18, $oy + 22, $cellw * 0.45, 12, 'C7D2FE' );
	// Shield with a check mark.
	$sx = (int) ( $ox + $cellw - 30 );
	$sy = (int) ( $oy - 20 );
	$sr = 58;
	imagefilledpolygon(
		$im,
		array( $sx, $sy - $sr, $sx + $sr, $sy - $sr * 0.6, $sx + $sr * 0.85, $sy + $sr * 0.5, $sx, $sy + $sr, $sx - $sr * 0.85, $sy + $sr * 0.5, $sx - $sr, $sy - $sr * 0.6 ),
		qvimg_color( $im, '2DD4BF' )
	);
	qvimg_polyline( $im, array( array( $sx - 26, $sy ), array( $sx - 6, $sy + 22 ), array( $sx + 28, $sy - 22 ) ), '052E2B', 10 );
	return $im;
}

/**
 * Article: CQRS, commands to a write model and queries from a read model.
 */
function qvimg_cover_cqrs( $w, $h, $accent, $accent2 ) {
	$im    = qvimg_cover_bg( $w, $h, $accent, $accent2 );
	$panel = '0D1440';
	$bw    = (int) ( $w * 0.2 );
	$bh    = (int) ( $h * 0.2 );
	$left  = (int) ( $w * 0.12 );
	$right = (int) ( $w * 0.68 );
	$top   = (int) ( $h * 0.18 );
	$bot   = (int) ( $h * 0.62 );
	$label = static function ( $x, $y, $bw, $text, $hex ) use ( $im ) {
		$size = 34;
		$tw   = qvimg_text_width( $size, $text, true );
		qvimg_text( $im, $size, $x + ( $bw - $tw ) / 2, $y, $hex, $text, true );
	};
	// App (centre) sends commands up and asks queries down.
	$ax = (int) ( $w / 2 - $bw * 0.6 );
	$ay = (int) ( $h / 2 - $bh * 0.55 );
	qvimg_rrect_gradient( $im, $ax, $ay, (int) ( $bw * 1.2 ), (int) ( $bh * 1.1 ), 30, $accent, $accent2 );
	$label( $ax, $ay + $bh * 0.68, $bw * 1.2, 'App', 'FFFFFF' );
	// Write side.
	qvimg_rrect( $im, $left, $top, $bw, $bh, 28, qvimg_mix( $panel, $accent, 0.25 ) );
	$label( $left, $top + $bh * 0.62, $bw, 'Write', qvimg_mix( $accent, 'FFFFFF', 0.4 ) );
	// Read side.
	qvimg_rrect( $im, $right, $bot, $bw, $bh, 28, qvimg_mix( $panel, $accent2, 0.3 ) );
	$label( $right, $bot + $bh * 0.62, $bw, 'Read', qvimg_mix( $accent2, 'FFFFFF', 0.5 ) );
	// Database cylinders under each side.
	foreach ( array( array( $left + $bw / 2, $top + $bh + 120, $accent ), array( $right + $bw / 2, $bot - 120, $accent2 ) ) as $db ) {
		list( $dx, $dy, $hex ) = $db;
		$c = qvimg_color( $im, qvimg_mix( $panel, $hex, 0.45 ) );
		imagefilledrectangle( $im, (int) ( $dx - 70 ), (int) ( $dy - 40 ), (int) ( $dx + 70 ), (int) ( $dy + 40 ), $c );
		imagefilledellipse( $im, (int) $dx, (int) ( $dy + 40 ), 140, 44, $c );
		imagefilledellipse( $im, (int) $dx, (int) ( $dy - 40 ), 140, 44, qvimg_color( $im, qvimg_mix( $hex, 'FFFFFF', 0.2 ) ) );
	}
	imagesetthickness( $im, 6 );
	qvimg_arrow( $im, $ax, $ay + 20, $left + $bw + 24, $top + $bh / 2, qvimg_mix( $accent, 'FFFFFF', 0.3 ) );
	qvimg_arrow( $im, $right, $bot + $bh / 2, $ax + $bw * 1.2 + 24, $ay + $bh * 1.1 - 20, qvimg_mix( $accent2, 'FFFFFF', 0.4 ) );
	qvimg_text( $im, 26, $left + $bw + 80, $top + $bh / 2 + 80, 'C7D2FE', 'commands' );
	qvimg_text( $im, 26, $right - 200, $bot + $bh / 2 - 50, 'C7D2FE', 'queries' );
	return $im;
}

/**
 * Article: stock levels with a threshold line and an alert bell.
 */
function qvimg_cover_stock( $w, $h, $accent, $accent2 ) {
	$im    = qvimg_cover_bg( $w, $h, $accent, $accent2 );
	$panel = '0D1440';
	$px    = (int) ( $w * 0.14 );
	$py    = (int) ( $h * 0.14 );
	$pw    = (int) ( $w * 0.72 );
	$ph    = (int) ( $h * 0.72 );
	qvimg_rrect( $im, $px, $py, $pw, $ph, 36, $panel );
	$base   = $py + $ph - 70;
	$levels = array( 0.82, 0.64, 0.74, 0.22, 0.58, 0.9 );
	$n      = count( $levels );
	$slot   = ( $pw - 120 ) / $n;
	$limit  = $base - ( $ph - 160 ) * 0.35;
	foreach ( $levels as $i => $lv ) {
		$bh  = ( $ph - 160 ) * $lv;
		$bx  = $px + 60 + $i * $slot + $slot * 0.18;
		$low = $lv < 0.35;
		if ( $low ) {
			qvimg_rrect( $im, $bx, $base - $bh, (int) ( $slot * 0.64 ), (int) $bh, 14, '93C5FD' );
		} else {
			qvimg_rrect_gradient( $im, $bx, (int) ( $base - $bh ), (int) ( $slot * 0.64 ), (int) $bh, 14, $accent, $accent2 );
		}
		// Box icon on top of each column.
		qvimg_rrect( $im, $bx + $slot * 0.12, $base - $bh - 54, (int) ( $slot * 0.4 ), 40, 8, qvimg_mix( $panel, 'FFFFFF', 0.18 ) );
		imageline( $im, (int) ( $bx + $slot * 0.32 ), (int) ( $base - $bh - 54 ), (int) ( $bx + $slot * 0.32 ), (int) ( $base - $bh - 14 ), qvimg_color( $im, $panel ) );
	}
	// Threshold (dashed).
	$c = qvimg_color( $im, 'C7D2FE' );
	imagesetthickness( $im, 4 );
	for ( $x = $px + 40; $x < $px + $pw - 40; $x += 36 ) {
		imageline( $im, (int) $x, (int) $limit, (int) ( $x + 18 ), (int) $limit, $c );
	}
	imagesetthickness( $im, 1 );
	// Bell badge above the low column.
	$lx = (int) ( $px + 60 + 3 * $slot + $slot * 0.5 );
	$ly = (int) ( $limit - 150 );
	imagefilledellipse( $im, $lx, $ly, 150, 150, qvimg_color( $im, '2DD4BF' ) );
	$bell = '052E2B';
	imagefilledarc( $im, $lx, $ly + 6, 70, 80, 180, 360, qvimg_color( $im, $bell ), IMG_ARC_PIE );
	imagefilledrectangle( $im, $lx - 35, $ly + 6, $lx + 35, $ly + 26, qvimg_color( $im, $bell ) );
	qvimg_rrect( $im, $lx - 46, $ly + 22, 92, 12, 6, $bell );
	imagefilledellipse( $im, $lx, $ly + 44, 22, 22, qvimg_color( $im, $bell ) );
	return $im;
}

/**
 * Initials avatar.
 */
function qvimg_avatar( $size, $initials, $accent, $accent2 ) {
	$im = qvimg_canvas( $size, $size );
	qvimg_gradient( $im, qvimg_mix( '0D1440', $accent, 0.55 ), qvimg_mix( '0D1440', $accent2, 0.75 ) );
	qvimg_glow( $im, (int) ( $size * 0.8 ), (int) ( $size * 0.15 ), (int) ( $size * 0.6 ), 'FFFFFF', 2 );
	$fs = (int) ( $size * 0.3 );
	$tw = qvimg_text_width( $fs, $initials, true );
	qvimg_text( $im, $fs, ( $size - $tw ) / 2, $size / 2 + $fs * 0.36, 'FFFFFF', $initials, true );
	return $im;
}

/**
 * Save as JPEG (photos/mockups) or PNG.
 */
function qvimg_save( $im, $path, $quality = 86 ) {
	wp_mkdir_p( dirname( $path ) );
	if ( str_ends_with( $path, '.png' ) ) {
		imagepng( $im, $path, 7 );
	} else {
		imagejpeg( $im, $path, $quality );
	}
	imagedestroy( $im );
	return $path;
}
